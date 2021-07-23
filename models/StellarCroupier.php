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
    // croupier balance percent reserved, so prize won't exceed (100 - self::PRIZE_RESERVE_PERCENT) % of the balance
    public const PRIZE_RESERVE_PERCENT = 20; // %
    public const PRIZE_RETURN_PERCENT = 95; // %
    public const WINNER_RATES = [2, 3, 4, 5, 10, 20, 50, 100, 500, 1000, 10000, 100000, 1000000];

    private ?Account $croupierAccount = null;
    private float $croupierBalance;

    /**
     * @return float
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     */
    public function getCroupierBalance(): float
    {
        if (!isset($this->croupierAccount)) {
            $this->updateCroupierAccount();
        }
        return $this->croupierBalance;
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
     * @param int $limit maximum 200
     * @return array
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     */
    public function getBets(?string $sinceCursor = null, int $limit = 50): array
    {
        if ($limit < 1 || $limit > 200) {
            throw new InvalidArgumentException('$limit should be in range 1..200');
        }

        if (!isset($this->croupierAccount)) {
            $this->updateCroupierAccount();
        }

        return array_map(
            fn (Payment $p) => [
                'player_public_key' => $p->getFromAccountId(),
                'bet_amount' => $p->getAmount()->getBalance(),
                'paging_token' => $p->getPagingToken(),
            ],
            array_filter(
                $this->croupierAccount->getPayments($sinceCursor), // gets at most 50 payments
                fn ($p) =>
                    get_class($p) === Payment::class
                    && $p->getToAccountId() == self::getCroupierPublicKey()
                    && $p->isNativeAsset()
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
    public function sendPrizesToPlayers(): array
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
                    'player_public_key' => $playerPublicKey,
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
     * Returns true if successfully send bet to Croupier account
     *
     * @param string $sourcePublicKey
     * @param string $sourcePrivateKey
     * @param float $amount
     * @return bool
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\PostTransactionException
     */
    public function makeBet(string $sourcePublicKey, string $sourcePrivateKey, float $amount): bool
    {
        $response = $this
            ->buildTransaction($sourcePublicKey)
            ->addLumenPayment(self::getCroupierPublicKey(), $amount)
            ->submit($sourcePrivateKey);
        return $response->getResult()->succeeded();
    }

    /**
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     */
    private function updateCroupierAccount()
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
            ->buildTransaction(StellarServer::getCroupierPublicKey())
            ->addLumenPayment($destinationPublicKey, $amount)
            ->setTextMemo(sprintf(self::PRIZE_MEMO_TEXT, $winnerRate))
            ->submit(StellarServer::getCroupierPrivateKey());
        $this->croupierBalance -= $amount + $response->getResult()->getFeeCharged()->getScaledValue();
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
        $croupierBalance = $this->getCroupierBalance();

        if ($betAmount < self::BET_MINIMUM_AMOUNT) {
            return [];
        }

        $winnerRate = self::WINNER_RATES[array_rand(self::WINNER_RATES)];
        $winChance = (self::PRIZE_RETURN_PERCENT / 100) / $winnerRate;

        if (!self::generateBool($winChance)) {
            return [];
        }

        $prePrizeAmount = $betAmount * $winnerRate;
        $prizeAmount = min($prePrizeAmount, self::maximalPrizeAmount($croupierBalance));

        if ($prizeAmount < 0.000_000_1) {
            return [];
        }

        if ($this->isTestnet()) {
            $prizeAmount = 0.001;
        }

        return [
            'winner_rate' => $winnerRate,
            'prize_amount' => $prizeAmount,
        ];
    }

    private static function generateBool(float $probability): bool
    {
        $rand = lcg_value(); // in range [0; 1]
        return $rand <= $probability;
    }

    private static function maximalPrizeAmount(float $balance): float
    {
        $balance -= self::BALANCE_RESERVE_AMOUNT;
        $balance *= (1 - (self::PRIZE_RESERVE_PERCENT / 100));
        if ($balance < 0.01) {
            $balance = 0;
        }
        return $balance;
    }
}
