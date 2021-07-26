<?php

namespace app\models;

use yii\base\InvalidArgumentException;
use ZuluCrypto\StellarSdk\Model\Account;
use ZuluCrypto\StellarSdk\Model\Payment;

class StellarCroupier extends StellarServer
{
    private const PRIZE_MEMO_TEXT = 'x%d Winner Prize';

    public const BET_MINIMUM_AMOUNT = 0.001; // XLM
    // minimum croupier balance that is reserved for account staying active
    public const BALANCE_RESERVE_AMOUNT = 5; // XLM
    // croupier balance percent reserved, so any prize won't exceed (100 - self::PRIZE_RESERVE_PERCENT) % of the balance
    public const PRIZE_RESERVE_PERCENT = 20; // %
    public const PRIZE_RETURN_PERCENT = 95; // %
    public const WINNER_RATES = [2, 3, 4, 5, 10, 20, 50, 100, 500, 1000, 10000, 100000, 1000000];

    private ?Account $croupierAccount = null;
    private ?float $croupierBalance = null;

    /**
     * @return float
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     */
    public function getCroupierBalance(): float
    {
        if (!isset($this->croupierBalance)) {
            $this->reloadCroupierAccount();
        }

        return $this->croupierBalance;
    }

    /**
     * @return \ZuluCrypto\StellarSdk\Model\Account
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     */
    public function getCroupierAccount(): Account
    {
        if (!isset($this->croupierAccount)) {
            $this->reloadCroupierAccount();
        }

        return $this->croupierAccount;
    }

    /**
     * Get bets from Stellar (proper payments to Croupier account) since provided cursor
     *
     * Returns array with scheme
     *
     * ```php
     * [
     *     [
     *         'player_public_key' => string,
     *         'bet_amount' => float,
     *         'paging_token' => int,
     *     ]
     * ]
     * ```
     * @param string|null $sinceCursor can be paging_token of bet
     * @param int $limit
     * @return array
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     */
    public function getBets(?string $sinceCursor = null, int $limit = 200): array
    {
        // in range [1..200]
        if ($limit < 1) {
            $limit = 1;
        } elseif ($limit > 200) {
            $limit = 200;
        }

        return array_map(
            fn (Payment $p) => [
                'player_public_key' => $p->getFromAccountId(),
                'bet_amount' => $p->getAmount()->getBalance(),
                'paging_token' => $p->getPagingToken(),
            ],
            array_filter(
                $this->getCroupierAccount()->getPayments($sinceCursor, $limit),
                fn ($p) =>
                    get_class($p) === Payment::class
                    && $p->getToAccountId() == self::getCroupierPublicKey()
                    && $p->isNativeAsset()
                    && $p->getAmount()->getBalance() >= self::BET_MINIMUM_AMOUNT
            )
        );
    }

    /**
     * Returns array with scheme
     *
     * ```php
     * [
     *     'bets_count' => int,
     *     'wins' => [
     *         [
     *              'player_public_key' => string,
     *              'prize_amount' => float,
     *              'winner_rate' => int,
     *         ]
     *     ]
     * ]
     * ```
     *
     * @return array
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\PostTransactionException
     */
    public function processingBets(): array
    {
        $sinceCursor = StellarCroupierData::getLastPagingToken();

        $bets = $this->getBets($sinceCursor);
        $wins = [];

        foreach ($bets as [
            'player_public_key' => $playerPublicKey,
            'bet_amount' => $betAmount,
            'paging_token' => $pagingToken,
        ]) {
            if ($result = $this->prizeAmount($betAmount)) {
                $this->sendPrize($playerPublicKey, $result['prize_amount'], $result['winner_rate']);
                $wins[] = [
                    'prize_amount' => $result['prize_amount'],
                    'winner_rate' => $result['winner_rate'],
                ];
            }

            StellarCroupierData::setLastPagingToken($pagingToken);
        }

        return [
            'bets_count' => count($bets),
            'wins' => $wins,
        ];
    }

    /**
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     */
    private function reloadCroupierAccount()
    {
        $this->croupierAccount = $this->getAccount(self::getCroupierPublicKey());
        $this->croupierBalance = $this->croupierAccount->getNativeBalance();
    }

    /**
     * @param string $destinationPublicKey
     * @param float $amount
     * @param int $winnerRate
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\PostTransactionException
     */
    private function sendPrize(string $destinationPublicKey, float $amount, int $winnerRate)
    {
        $response = $this
            ->buildTransaction(self::getCroupierPublicKey())
            ->addLumenPayment($destinationPublicKey, $amount)
            ->setTextMemo(sprintf(self::PRIZE_MEMO_TEXT, $winnerRate))
            ->submit(self::getCroupierPrivateKey());

        $this->croupierBalance = $this->getCroupierBalance() - ($amount + $response->getResult()->getFeeCharged()->getScaledValue());
    }

    /**
     * Prize amount based on bet amount, croupier balance, randomly chose win rate and randomly chose option
     * (has won or has lost)
     *
     * Returns array with scheme
     *
     * ```php
     * [
     *    'winner_rate' => int,
     *    'prize_amount' => float,
     * ]
     * ```
     *
     * If player lost, empty array returned
     *
     * @param float $betAmount
     * @return array
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     */
    private function prizeAmount(float $betAmount): array
    {
        if ($betAmount < self::BET_MINIMUM_AMOUNT) {
            return [];
        }

        $winnerRate = self::WINNER_RATES[array_rand(self::WINNER_RATES)];
        $winChance = (self::PRIZE_RETURN_PERCENT / 100) / $winnerRate;

        if (!self::generateBool($winChance)) {
            return [];
        }

        if (!$this->isTestnet()) {
            $prizeAmount = $betAmount * $winnerRate;
            $prizeAmount = min($prizeAmount, $this->getPrizeBalance());
        } else {
            $prizeAmount = 0.001;
        }

        return [
            'winner_rate' => $winnerRate,
            'prize_amount' => $prizeAmount,
        ];
    }

    private static function generateBool(float $probability): bool
    {
        // in range [0..1]
        $rand = lcg_value();

        return $rand <= $probability;
    }

    private function getPrizeBalance(): float
    {
        $balance = $tis->getCroupierBalance() - self::BALANCE_RESERVE_AMOUNT;
        $balance *= (1 - (self::PRIZE_RESERVE_PERCENT / 100));

        if ($balance < 0.01) {
            $balance = 0;
        }

        return $balance;
    }
}
