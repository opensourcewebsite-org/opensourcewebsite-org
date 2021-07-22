<?php


namespace app\models;


use yii\base\InvalidArgumentException;
use ZuluCrypto\StellarSdk\Model\Account;
use ZuluCrypto\StellarSdk\Model\Payment;

class StellarCroupier extends StellarServer
{
    public const PRIZE_MEMO_TEXT = 'x%d Winner Prize';

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
    public function sendPrize(string $destinationPublicKey, float $amount, int $winnerRate)
    {
        $response = $this
            ->buildTransaction(StellarServer::getCroupierPublicKey())
            ->addLumenPayment($destinationPublicKey, $amount)
            ->setTextMemo(sprintf(self::PRIZE_MEMO_TEXT, $winnerRate))
            ->submit(StellarServer::getCroupierPrivateKey());
        $this->croupierBalance -= $amount + $response->getResult()->getFeeCharged()->getScaledValue();
    }
}
