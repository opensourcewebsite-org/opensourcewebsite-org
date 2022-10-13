<?php

namespace app\models;

use ZuluCrypto\StellarSdk\Model\Account;
use ZuluCrypto\StellarSdk\Model\Payment;

class StellarCroupier extends StellarServer
{
    private const PRIZE_MEMO_TEXT = 'x%d Winner Prize';

    public const BET_MINIMUM_AMOUNT = 0.001; // XLM
    // minimum balance that is reserved for account staying active
    public const BALANCE_RESERVE_AMOUNT = 10; // XLM
    // balance percent reserved, so any prize won't exceed (100 - self::PRIZE_RESERVE_PERCENT) % of the balance
    public const PRIZE_RESERVE_PERCENT = 20; // %
    // percentage return to player (% RTP), the expected percentage of wagers that a game will return to the player in the long run
    public const PRIZE_RETURN_PERCENT = 95; // %
    // list of prizes, any prize is the player's bet multiplied by one of winner rate
    public const WINNER_RATES = [2, 3, 4, 5, 10, 20, 50, 100, 500, 1000, 10000, 100000, 1000000];

    private ?Account $account = null;

    private ?float $balance = null;

    /**
     * @return float
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     */
    public function getBalance(): float
    {
        if (!isset($this->balance)) {
            $this->reloadAccount();
        }

        return $this->balance;
    }

    /**
     * @return \ZuluCrypto\StellarSdk\Model\Account
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     */
    public function getAccount($accountId = null): Account
    {
        if (!isset($this->account)) {
            $this->reloadAccount();
        }

        return $this->account;
    }

    /**
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     */
    private function reloadAccount()
    {
        $this->account = parent::getAccount(self::getCroupierPublicKey());
        $this->balance = $this->account->getNativeBalance();
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
    public function processBets(): array
    {
        $sinceCursor = StellarCroupierData::getLastPagingToken();
        $limit = 200;
        $payments = $this->getAccount()->getPayments($sinceCursor, $limit);

        $wins = [];
        $betsCount = 0;

        foreach ($payments as $payment) {
            $pagingToken = $payment->getPagingToken();

            if (self::isBet($payment)) {
                /** @var Payment $payment */
                $playerPublicKey = $payment->getFromAccountId();
                $betAmount = $payment->getAmount()->getBalance();

                if ($result = $this->prizeAmount($betAmount)) {
                    $this->sendPrize($playerPublicKey, $result['prize_amount'], $result['winner_rate']);

                    $wins[] = [
                        'prize_amount' => $result['prize_amount'],
                        'winner_rate' => $result['winner_rate'],
                    ];
                }

                $betsCount++;
            }

            StellarCroupierData::setLastPagingToken($pagingToken);
        }

        return [
            'bets_count' => $betsCount,
            'wins' => $wins,
        ];
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

        $this->croupierBalance = $this->getBalance() - ($amount + $response->getResult()->getFeeCharged()->getScaledValue());
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
            $prizeAmount = min($prizeAmount, $this->getAvailableBalance());

            if ($prizeAmount < 0.001) {
                return [];
            }
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

    /**
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     * @throws \ErrorException
     */
    public function getAvailableBalance(): float
    {
        $balance = $this->getBalance() - self::BALANCE_RESERVE_AMOUNT;
        $balance *= (1 - (self::PRIZE_RESERVE_PERCENT / 100));

        if ($balance < self::BET_MINIMUM_AMOUNT) {
            $balance = 0;
        }

        return $balance;
    }

    private static function isBet($operation): bool
    {
        return get_class($operation) === Payment::class
            && $operation->getToAccountId() == self::getCroupierPublicKey()
            && $operation->isNativeAsset()
            && $operation->getAmount()->getBalance() >= self::BET_MINIMUM_AMOUNT;
    }
}
