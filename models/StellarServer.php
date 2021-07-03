<?php

namespace app\models;

use Cassandra\Date;
use DateTime;
use Yii;
use ZuluCrypto\StellarSdk\Horizon\ApiClient;
use ZuluCrypto\StellarSdk\Horizon\Exception\PostTransactionException;
use ZuluCrypto\StellarSdk\Model\Payment;
use ZuluCrypto\StellarSdk\Server;
use ZuluCrypto\StellarSdk\Util\MathSafety;
use ZuluCrypto\StellarSdk\XdrModel\Asset;
use ZuluCrypto\StellarSdk\XdrModel\Operation\PaymentOp;

class StellarServer extends Server
{
    public const INTEREST_RATE_WEEKLY = 0.5 / 100;

    public const MEMO_TEXT = 'Thanks For Your Deposit';

    // ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
    public const INCOME_WEEK_DAY = 'Friday';

    public const MINIMUM_BALANCES = [
        'EUR' => 50,
        'USD' => 50,
        'THB' => 50,
        'RUB' => 50
    ];

    public function __construct()
    {
        if (!isset(Yii::$app->params['stellar'])) {
            throw new \Exception('No stellar params');
        }

        if (isset(Yii::$app->params['stellar']['testNet']) && Yii::$app->params['stellar']['testNet']) {
            parent::__construct(ApiClient::newTestnetClient());
            $this->isTestnet = true;
        } else {
            parent::__construct(ApiClient::newPublicClient());
        }
    }

    public function operationExists(string $sourceId, string $destinationId, int $timeLowerBound, int $timeUpperBound): bool
    {
        $timeLowerBound = (new DateTime())->setTimestamp($timeLowerBound);
        $timeUpperBound = (new DateTime())->setTimestamp($timeUpperBound);

        return !empty(array_filter(
            $this->getAccount($sourceId)->getTransactions(null, 10, 'desc'),
            fn ($t) =>
                $t->getCreatedAt() >= $timeLowerBound
                && $t->getCreatedAt() <= $timeUpperBound
                && !empty(array_filter(
                    $t->getPayments(null, 10, 'desc'),
                    fn ($p) =>
                        get_class($p) === Payment::class
                        && $p->isNativeAsset()
                        && $p->getAmount()->getBalance() > 0
                        && $p->getFromAccountId() === $sourceId
                        && $p->getToAccountId() === $destinationId
                ))
        ));
    }

    public static function getIssuerPublicKey(): ?string
    {
        return Yii::$app->params['stellar']['issuer_public_key'] ?? null;
    }

    public static function getDistributorPublicKey(): ?string
    {
        return Yii::$app->params['stellar']['distributor_public_key'] ?? null;
    }

    public static function getDistributorPrivateKey(): ?string
    {
        return Yii::$app->params['stellar']['distributor_private_key'] ?? null;
    }

    public static function getOperatorPublicKey(): ?string
    {
        return Yii::$app->params['stellar']['operator_public_key'] ?? null;
    }

    public static function getOperatorPrivateKey(): ?string
    {
        return Yii::$app->params['stellar']['operator_private_key'] ?? null;
    }

    /**
     * @param string $assetCode
     * @param float $minimumBalance
     * @return \ZuluCrypto\StellarSdk\Model\Account[]
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     */
    public function getAssetHolders(string $assetCode, float $minimumBalance): array
    {
        MathSafety::require64Bit();

        $assetIssuerId = self::getIssuerPublicKey();
        $blacklist = [self::getDistributorPublicKey(), self::getOperatorPublicKey()];

        return array_filter(
            $this->getAccountsForAsset($assetCode, $assetIssuerId, 'asc', 200),
            fn ($a) => !in_array($a->getAccountId(), $blacklist)
                && !empty(array_filter(
                    $a->getBalances(),
                    fn ($b) => $b->getAssetCode() === $assetCode
                        && $b->getAssetIssuerAccountId() === $assetIssuerId
                        && $b->getBalance() >= $minimumBalance
                ))
        );
    }

    public static function incomeWeekly(float $balance): float
    {
        return floor($balance * self::INTEREST_RATE_WEEKLY * 100.0) / 100.0;
    }

    /**
     * @param string $assetCode
     * @param \ZuluCrypto\StellarSdk\Model\Account[] $destinations
     * @return string[] codes for each operation in transaction for all transactions
     * @throws \ErrorException
     */
    public function sendIncomeToAssetHolders(string $assetCode, array $destinations): array
    {
        MathSafety::require64Bit();

        $memoText = self::MEMO_TEXT;

        $TRANSACTION_LIMIT = 100;

        $assetIssuerId = self::getIssuerPublicKey();
        $publicKey = self::getDistributorPublicKey();
        $privateKey = self::getOperatorPrivateKey();
        $asset = Asset::newCustomAsset($assetCode, $assetIssuerId);

        $payments = array_map(
            fn ($d) => PaymentOp::newCustomPayment(
                $d->getAccountId(),
                self::incomeWeekly($d->getCustomAssetBalanceValue($asset)),
                $assetCode,
                $assetIssuerId,
                $publicKey
            ),
            $destinations
        );

        $results = [];

        foreach (array_chunk($payments, $TRANSACTION_LIMIT) as $paymentGroup) {
            $transaction = $this->buildTransaction($publicKey);
            foreach ($paymentGroup as $payment) {
                $transaction = $transaction->addOperation($payment);
            }
            $transaction = $transaction
                ->setTextMemo($memoText);

            try {
                $response = $transaction->submit($privateKey);
                $results += array_map(
                    fn ($r) => $r->getErrorCode(),
                    $response->getResult()->getOperationResults()
                );
            } catch (PostTransactionException $e) {
                $results += array_map(
                    fn ($r) => $r->getErrorCode(),
                    $e->getResult()->getOperationResults()
                );
            }
        }

        return $results;
    }

    /**
     * Next date of income for asset holders. Accessed via data from distributor Stellar account
     * @return \DateTime
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     */
    public function getNextPaymentDate(): DateTime
    {
        $paymentDate = $this->getAccountDataByKey(StellarServer::getDistributorPublicKey(), 'next_payment_date');
        $today = new DateTime('today');
        $nextWeekDay = new DateTime('next ' . self::INCOME_WEEK_DAY);
        if (!isset($paymentDate)) {
            $paymentDate = $today->format('l') === self::INCOME_WEEK_DAY ? $today : $nextWeekDay;
        } else {
            $paymentDate = DateTime::createFromFormat('Y-m-d|', $paymentDate);
        }
        if ($paymentDate < $today) {
            $paymentDate = $nextWeekDay;
        }
        return $paymentDate;
    }

    public function setNextPaymentDate(DateTime $nextPaymentDate): void
    {
        $this
            ->buildTransaction(self::getDistributorPublicKey())
            ->setAccountData('next_payment_date', $nextPaymentDate->format('Y-m-d'))
            ->submit(self::getOperatorPrivateKey());
    }
}
