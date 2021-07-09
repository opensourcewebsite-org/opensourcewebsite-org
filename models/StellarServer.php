<?php

namespace app\models;

use DateTime;
use GuzzleHttp\Exception\ServerException;
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
    // ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
    public const INCOME_WEEK_DAY = 'Friday';

    public const INCOME_MEMO_TEXT = 'Thanks For Your Deposit';

    public const MINIMUM_BALANCES = [
        'EUR' => 50,
        'USD' => 50,
        'THB' => 1000,
        'RUB' => 2000,
        'UAH' => 1000,
    ];

    public const INTEREST_RATE_WEEKLY = 0.5 / 100;

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

    public static function getOperatorPublicKey(): ?string
    {
        return Yii::$app->params['stellar']['operator_public_key'] ?? null;
    }

    public static function getOperatorPrivateKey(): ?string
    {
        return Yii::$app->params['stellar']['operator_private_key'] ?? null;
    }

    public function isTestnet(): bool
    {
        return $this->isTestnet;
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

        $blacklist = [
            self::getDistributorPublicKey(),
            self::getOperatorPublicKey(),
        ];

        return array_filter(
            $this->getAccountsForAsset($assetCode, self::getIssuerPublicKey(), 'asc', 200),
            fn ($a) => !in_array($a->getAccountId(), $blacklist)
                && !empty(array_filter(
                    $a->getBalances(),
                    fn ($b) => $b->getAssetCode() === $assetCode
                        && $b->getAssetIssuerAccountId() === self::getIssuerPublicKey()
                        && $b->getBalance() >= $minimumBalance
                ))
        );
    }

    public static function incomeWeekly(float $balance): float
    {
        if (Yii::$app->params['stellar']['testNet'] ?? false) {
            return 0.01;
        }

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

        $TRANSACTION_LIMIT = 100;

        $asset = Asset::newCustomAsset($assetCode, self::getIssuerPublicKey());

        $payments = array_map(
            fn ($d) => PaymentOp::newCustomPayment(
                $d->getAccountId(),
                self::incomeWeekly($d->getCustomAssetBalanceValue($asset)),
                $assetCode,
                self::getIssuerPublicKey(),
                self::getDistributorPublicKey()
            ),
            $destinations
        );

        $results = [];

        foreach (array_chunk($payments, $TRANSACTION_LIMIT) as $paymentGroup) {
            $transaction = $this->buildTransaction(self::getDistributorPublicKey());

            foreach ($paymentGroup as $payment) {
                $transaction = $transaction->addOperation($payment);
            }

            $transaction = $transaction
                ->setTextMemo(self::INCOME_MEMO_TEXT);

            $sleepDuration = 5; // seconds
            while (true) {
                try {
                    $response = $transaction->submit(self::getOperatorPrivateKey());
                    $results += array_map(
                        fn ($r) => $r->getErrorCode(),
                        $response->getResult()->getOperationResults()
                    );
                } catch (PostTransactionException $e) {
                    $results += array_map(
                        fn ($r) => $r->getErrorCode(),
                        $e->getResult()->getOperationResults()
                    );
                } catch (ServerException $e) {
                    if ($e->getCode() === 504) {
                        sleep($sleepDuration);
                        $sleepDuration += 5;
                        if ($sleepDuration >= 30) {
                            throw $e;
                        }
                        continue;
                    }
                    throw $e;
                }
                break;
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
        $paymentDate = $this->getAccountDataByKey(self::getDistributorPublicKey(), 'next_payment_date');

        $today = new DateTime('today');
        $nextWeekDay = new DateTime('next ' . self::INCOME_WEEK_DAY);
        $needToSet = false;

        if (!$paymentDate) {
            $paymentDate = $today->format('l') === self::INCOME_WEEK_DAY ? $today : $nextWeekDay;
            $needToSet = true;
        } else {
            $paymentDate = DateTime::createFromFormat('Y-m-d|', $paymentDate);
            if ($paymentDate < $today) {
                $paymentDate = $nextWeekDay;
                $needToSet = true;
            }
        }

        if ($needToSet) {
            $this->setNextPaymentDate($paymentDate);
        }

        return $paymentDate;
    }

    public function setNextPaymentDate(?DateTime $nextPaymentDate = null): void
    {
        if (!$nextPaymentDate) {
            $nextPaymentDate = new DateTime('next ' . self::INCOME_WEEK_DAY);
        }

        $this
            ->buildTransaction(self::getDistributorPublicKey())
            ->setAccountData('next_payment_date', $nextPaymentDate->format('Y-m-d'))
            ->submit(self::getOperatorPrivateKey());
    }
}
