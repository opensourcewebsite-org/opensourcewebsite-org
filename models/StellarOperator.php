<?php

namespace app\models;

use DateInterval;
use DateTime;
use GuzzleHttp\Exception\ServerException;
use ZuluCrypto\StellarSdk\Horizon\Exception\PostTransactionException;
use ZuluCrypto\StellarSdk\Transaction\TransactionBuilder;
use ZuluCrypto\StellarSdk\Util\MathSafety;
use ZuluCrypto\StellarSdk\XdrModel\Asset;
use ZuluCrypto\StellarSdk\XdrModel\Operation\PaymentOp;
use function Functional\group;

class StellarOperator extends StellarServer
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
        // TODO add pagination for big response list
        return array_filter(
            $this->getAccountsForAsset($assetCode, self::getIssuerPublicKey(), 'asc', 100),
            fn ($a) => !in_array($a->getAccountId(), $blacklist)
                && !empty(array_filter(
                    $a->getBalances(),
                    fn ($b) => $b->getAssetCode() === $assetCode
                        && $b->getAssetIssuerAccountId() === self::getIssuerPublicKey()
                        && $b->getBalance() >= $minimumBalance
                ))
        );
    }

    /**
     * @param string $assetCode
     * @param float $minimumBalance
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     */
    public function fetchAndSaveAssetHolders(string $assetCode, float $minimumBalance): void
    {
        while (true) {
            $asset = Asset::newCustomAsset($assetCode, self::getIssuerPublicKey());
            $lastLedger = $this->getLastLedger();
            $holders = $this->getAssetHolders($assetCode, $minimumBalance);

            foreach ($holders as $holder) {
                if ($holder->getLastModifiedLedger() > $lastLedger) {
                    self::deleteIncomesDataFromDatabase($assetCode, new DateTime('today'));
                    continue 2; // goto `while (true) {` line
                }

                $income = new UserStellarIncome();
                $income->account_id = $holder->getAccountId();
                $income->asset_code = $assetCode;
                $income->income = $this->incomeWeekly($holder->getCustomAssetBalanceValue($asset));
                $income->save();
            }
            return;
        }
    }

    public function incomeWeekly(float $balance): float
    {
        if ($this->isTestnet()) {
            return 0.01;
        }

        return floor($balance * self::INTEREST_RATE_WEEKLY * 100.0) / 100.0;
    }

    /**
     * @param string $assetCode
     * @param \DateTime $date used to fetch asset holders from database with this date.
     * @return array with elements like
     * <code>$resultCode => ['accounts_count' => $accountsCount, 'income_sent' => $incomeSent]</code>
     * @throws \ErrorException
     */
    public function sendIncomeToAssetHolders(string $assetCode, DateTime $date): array
    {
        MathSafety::require64Bit();
        // TODO refactoring for db query for big amount of holders and dont use one array
        $destinations = self::getAssetHoldersFromDatabase($assetCode, $date);

        $payments = array_map(
            fn ($d) => PaymentOp::newCustomPayment(
                $d->account_id,
                $d->income,
                $assetCode,
                self::getIssuerPublicKey(),
                self::getDistributorPublicKey()
            ),
            $destinations
        );

        $operationResults = [];
        $transactionResults = [];

        foreach (array_chunk($payments, self::TRANSACTION_LIMIT) as $paymentGroup) {
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
                    $operationResults += array_map(
                        fn ($r) => $r->getErrorCode(),
                        $response->getResult()->getOperationResults()
                    );
                    $transactionResults[] = [
                        'status' => $response->getResult()->getResultCode(),
                        'accounts_count' => count($response->getResult()->getOperationResults()),
                        'income_sent' => array_sum(
                            array_map(fn (PaymentOp $p) => $p->getAmount()->getScaledValue(), $paymentGroup)
                        ),
                    ];
                } catch (PostTransactionException $e) {
                    $operationResults += array_map(
                        fn ($r) => $r->getErrorCode(),
                        $e->getResult()->getOperationResults()
                    );
                    $transactionResults[] = [
                        'status' => $e->getResult()->getResultCode(),
                        'accounts_count' => count($e->getResult()->getOperationResults()),
                        'income_sent' => 0,
                    ];
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

        $processed_at = time();
        foreach (array_map(null, $destinations, $operationResults) as [$holder, $result]) {
            $holder->processed_at = $processed_at;
            $holder->result_code = $result;
            $holder->save();
        }

        return array_map(
            fn ($ts) => array_reduce($ts, fn ($carry, $t) => [
                'accounts_count' => $carry['accounts_count'] + $t['accounts_count'],
                'income_sent' => $carry['income_sent'] + $t['income_sent'],
            ], [
                'accounts_count' => 0,
                'income_sent' => 0,
            ]),
            group($transactionResults, fn ($t) => $t['status'])
        );
    }

    /**
     * Next date of income for asset holders. Accessed via data from distributor Stellar account
     * @return \DateTime
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     * @throws \ErrorException
     * @throws \Exception
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

    /**
     * @param \DateTime|null $date
     * @return bool
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     * @throws \ErrorException
     */
    public function isPaymentDate(?DateTime $date = null): bool
    {
        $date = $date ?? new DateTime('today');

        return $this->getNextPaymentDate() == $date;
    }

    /**
     * @param \DateTime|null $nextPaymentDate
     * @throws \ErrorException
     * @throws \Exception
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\PostTransactionException
     */
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

    public static function incomesSentAlready(string $assetCode, DateTime $date): bool
    {
        $date->setTime(0, 0);
        $nextDay = (clone $date)->add(new DateInterval('P1D'));

        return UserStellarIncome::find()
            ->where([
                'asset_code' => $assetCode,
            ])
            ->andWhere([
                'between', 'created_at', $date->getTimestamp(), $nextDay->getTimestamp(),
            ])
            ->andWhere([
                'not', ['processed_at' => null],
            ])
            ->exists();
    }

    public static function deleteIncomesDataFromDatabase(string $assetCode, DateTime $date): void
    {
        $date->setTime(0, 0);
        $nextDay = (clone $date)->add(new DateInterval('P1D'));

        UserStellarIncome::deleteAll([
            'and',
            ['asset_code' => $assetCode],
            ['between', 'created_at', $date->getTimestamp(), $nextDay->getTimestamp()],
            ['processed_at' => null],
        ]);
    }

    /**
     * @param string $assetCode
     * @param \DateTime $date
     * @return UserStellarIncome[]
     */
    private static function getAssetHoldersFromDatabase(string $assetCode, DateTime $date): array
    {
        $date->setTime(0, 0);
        $nextDay = (clone $date)->add(new DateInterval('P1D'));

        return UserStellarIncome::find()
            ->where([
                'asset_code' => $assetCode,
                'processed_at' => null,
            ])
            ->andWhere([
                'between', 'created_at', $date->getTimestamp(), $nextDay->getTimestamp(),
            ])
            ->all();
    }
}
