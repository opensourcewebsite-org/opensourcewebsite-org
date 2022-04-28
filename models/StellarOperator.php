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

    public const INTEREST_RATE_WEEKLY = 0.1 / 100;

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
    public function fetchAndSaveRecipients(string $assetCode, float $minimumBalance): void
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

                $income = new UserStellarDepositIncome();
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
    public function sendIncomeToRecipients(string $assetCode, DateTime $date): void
    {
        MathSafety::require64Bit();
        // TODO refactoring for db query for big amount of holders and dont use one array
        $recipients = $this->getRecipients($date, $assetCode);

        foreach (array_chunk($recipients, self::TRANSACTION_LIMIT) as $recipientsGroup) {
            $transaction = $this->buildTransaction(self::getDistributorPublicKey());

            foreach ($recipientsGroup as $recipient) {
                $payment = PaymentOp::newCustomPayment(
                    $recipient->account_id,
                    $recipient->income,
                    $assetCode,
                    self::getIssuerPublicKey(),
                    self::getDistributorPublicKey()
                );

                $transaction = $transaction->addOperation($payment);
            }

            $transaction = $transaction->setTextMemo(self::INCOME_MEMO_TEXT);
            $processedAt = time();

            try {
                $response = $transaction->submit(self::getOperatorPrivateKey());

                foreach ($recipientsGroup as $recipient) {
                    $recipient->processed_at = $processedAt;
                    $recipient->save();
                }
            } catch (PostTransactionException $e) {
                foreach (array_map(null, $recipientsGroup, $e->getResult()->getOperationResults()) as [$recipient, $operationResult]) {
                    if ($operationResult && $operationResult->getErrorCode()) {
                        $recipient->processed_at = $processedAt;
                        $recipient->result_code = $operationResult->getErrorCode();
                        $recipient->save();
                    }
                }
            } catch (ServerException $e) {
                if ($e->getCode() === 504) {
                    return;
                }

                throw $e;
            }
        }
    }

    /**
     * Next date of income for asset holders. Accessed via data from distributor Stellar account
     * @return \DateTime
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     * @throws \ErrorException
     * @throws \Exception
     */
    public function getNextPaymentDate(): string
    {
        $today = new DateTime('today');
        $nextWeekDay = new DateTime('next ' . self::INCOME_WEEK_DAY);
        $needToSet = false;

        if (!$paymentDate = StellarDistributorData::getNextPaymentDate()) {
            $paymentDate = $this->getAccountDataByKey(self::getDistributorPublicKey(), 'next_payment_date');

            if ($paymentDate) {
                StellarDistributorData::setNextPaymentDate($paymentDate);
            }
        }

        if (!$paymentDate) {
            $paymentDate = ($today->format('l') == self::INCOME_WEEK_DAY) ? $today->format('Y-m-d') : $nextWeekDay->format('Y-m-d');
            $needToSet = true;
        } else {
            if (DateTime::createFromFormat('Y-m-d|', $paymentDate) < $today) {
                $paymentDate = $nextWeekDay->format('Y-m-d');
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
        if (!$date) {
            $date = new DateTime('today');
        }

        return DateTime::createFromFormat('Y-m-d|', $this->getNextPaymentDate()) == $date;
    }

    /**
     * @param \DateTime|null $nextPaymentDate
     * @throws \ErrorException
     * @throws \Exception
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\PostTransactionException
     */
    public function setNextPaymentDate(?string $nextPaymentDate = null): void
    {
        if (!$nextPaymentDate) {
            $nextPaymentDate = new DateTime('next ' . self::INCOME_WEEK_DAY);
            $nextPaymentDate = $nextPaymentDate->format('Y-m-d');
        }

        $this
            ->buildTransaction(self::getDistributorPublicKey())
            ->setAccountData('next_payment_date', $nextPaymentDate)
            ->submit(self::getOperatorPrivateKey());

        StellarDistributorData::setNextPaymentDate($nextPaymentDate);
    }

    public static function incomesSentAlready(string $assetCode, DateTime $date): bool
    {
        $date->setTime(0, 0);
        $nextDay = (clone $date)->add(new DateInterval('P1D'));

        return UserStellarDepositIncome::find()
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

        UserStellarDepositIncome::deleteAll([
            'and',
            ['asset_code' => $assetCode],
            ['between', 'created_at', $date->getTimestamp(), $nextDay->getTimestamp()],
            ['processed_at' => null],
        ]);
    }

    /**
     * @param \DateTime $date
     * @param string $assetCode
     * @return UserStellarDepositIncome[]
     */
    public function getRecipients(DateTime $date, string $assetCode = null): array
    {
        $date->setTime(0, 0);
        $nextDay = (clone $date)->add(new DateInterval('P1D'));

        $query = UserStellarDepositIncome::find()
            ->andWhere([
                'processed_at' => null,
            ])
            ->andWhere([
                'between', 'created_at', $date->getTimestamp(), $nextDay->getTimestamp(),
            ]);

        if ($assetCode) {
            $query->andWhere([
                'asset_code' => $assetCode,
            ]);
        }

        return $query->all();
    }
}
