<?php

namespace app\models;

use ZuluCrypto\StellarSdk\Model\Account;
use ZuluCrypto\StellarSdk\Model\Payment;
use DateInterval;
use DateTime;
use GuzzleHttp\Exception\ServerException;
use ZuluCrypto\StellarSdk\Horizon\Exception\PostTransactionException;
use ZuluCrypto\StellarSdk\Transaction\TransactionBuilder;
use ZuluCrypto\StellarSdk\Util\MathSafety;
use ZuluCrypto\StellarSdk\XdrModel\Operation\PaymentOp;
use function Functional\group;

class StellarGiver extends StellarServer
{
    // ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
    public const INCOME_WEEK_DAY = 'Friday';

    private const INCOME_MEMO_TEXT = 'Basic Income';
    // minimum possible payment to each participant
    public const PAYMENT_MINIMUM_AMOUNT = 0.0001; // XLM
    // minimum balance that is reserved for account staying active
    public const BALANCE_RESERVE_AMOUNT = 10; // XLM
    // % of the balance which is paid as weekly basic income to participants
    public const WEEKLY_PAYMENT_PERCENT = 1; // %
    // the count of votes of other participants to become a participant
    public const PARTICIPANT_MINIMUM_VOTES = 5;

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
        $this->account = parent::getAccount(self::getGiverPublicKey());
        $this->balance = $this->account->getNativeBalance();
    }

    /**
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     * @throws \ErrorException
     */
    public function getAvailableBalance(): float
    {
        $balance = $this->getBalance() - self::BALANCE_RESERVE_AMOUNT;
        $balance *= (self::WEEKLY_PAYMENT_PERCENT / 100);

        if ($balance < self::PAYMENT_MINIMUM_AMOUNT) {
            $balance = 0;
        }

        return $balance;
    }

    /**
     * @throws \ErrorException
     * @throws \ZuluCrypto\StellarSdk\Horizon\Exception\HorizonException
     */
    public function fetchAndSaveRecipients(): void
    {
        if ($paymentAmount = $this->getPaymentAmount()) {
            foreach ($this->getParticipants() as $user) {
                $income = new UserStellarBasicIncome();

                $income->account_id = $user->userStellar->getPublicKey();
                $income->income = $paymentAmount;
                $income->save();
            }
        }
    }

    /**
     * @param \DateTime $date used to fetch asset holders from database with this date.
     * @return array with elements like
     * <code>$resultCode => ['accounts_count' => $accountsCount, 'income_sent' => $incomeSent]</code>
     * @throws \ErrorException
     */
    public function sendIncomeToRecipients(DateTime $date): void
    {
        MathSafety::require64Bit();
        // TODO refactoring for db query for big amount of holders and dont use one array
        $recipients = $this->getRecipients($date);

        foreach (array_chunk($recipients, self::TRANSACTION_LIMIT) as $recipientsGroup) {
            $transaction = $this->buildTransaction(self::getGiverPublicKey());

            foreach ($recipientsGroup as $recipient) {
                $payment = PaymentOp::newNativePayment(
                    $recipient->account_id,
                    $recipient->income,
                    self::getGiverPublicKey()
                );

                $transaction = $transaction->addOperation($payment);
            }

            $transaction = $transaction->setTextMemo(self::INCOME_MEMO_TEXT);
            $processedAt = time();

            try {
                $response = $transaction->submit(self::getGiverPrivateKey());

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
     * Next payment date for participants. Accessed via data from stellar account.
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

        if (!$paymentDate = StellarGiverData::getNextPaymentDate()) {
            $paymentDate = $this->getAccountDataByKey(self::getGiverPublicKey(), 'next_payment_date');

            if ($paymentDate) {
                StellarGiverData::setNextPaymentDate($paymentDate);
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
        $date = $date ?? new DateTime('today');

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

        StellarGiverData::setNextPaymentDate($nextPaymentDate);

        $this
            ->buildTransaction(self::getGiverPublicKey())
            ->setAccountData('next_payment_date', $nextPaymentDate)
            ->submit(self::getGiverPrivateKey());
    }

    public static function incomesSentAlready(DateTime $date): bool
    {
        $date->setTime(0, 0);
        $nextDay = (clone $date)->add(new DateInterval('P1D'));

        return UserStellarBasicIncome::find()
            ->andWhere([
                'between', 'created_at', $date->getTimestamp(), $nextDay->getTimestamp(),
            ])
            ->andWhere([
                'not', ['processed_at' => null],
            ])
            ->exists();
    }

    public static function deleteIncomesDataFromDatabase(DateTime $date): void
    {
        $date->setTime(0, 0);
        $nextDay = (clone $date)->add(new DateInterval('P1D'));

        UserStellarBasicIncome::deleteAll([
            'and',
            ['between', 'created_at', $date->getTimestamp(), $nextDay->getTimestamp()],
            ['processed_at' => null],
        ]);
    }

    /**
     * @param \DateTime $date
     * @return UserStellarBasicIncome[]
     */
    public function getRecipients(DateTime $date): array
    {
        $date->setTime(0, 0);
        $nextDay = (clone $date)->add(new DateInterval('P1D'));

        return UserStellarBasicIncome::find()
            ->andWhere([
                'processed_at' => null,
            ])
            ->andWhere([
                'between', 'created_at', $date->getTimestamp(), $nextDay->getTimestamp(),
            ])
            ->all();
    }

    public static function getParticipantsQuery()
    {
        return User::find()
            ->where([
                'status' => User::STATUS_ACTIVE,
                'basic_income_on' => 1,
            ])
            ->andWhere([
                'not',
                ['basic_income_activated_at' => null],
            ])
            ->joinWith('stellar')
            ->andWhere([
                'not',
                [UserStellar::tableName() . '.confirmed_at' => null],
            ])
            ->orderBy([
                'rating' => SORT_DESC,
                'created_at' => SORT_ASC,
            ]);
    }

    public function getParticipants()
    {
        return self::getParticipantsQuery()->all();
    }

    public function getParticipantsCount()
    {
        return self::getParticipantsQuery()->count();
    }

    public function getPaymentAmount()
    {
        if ($this->getParticipantsCount() && $this->getAvailableBalance()) {
            $paymentAmount = floor(($this->getAvailableBalance() / $this->getParticipantsCount()) * 10000) / 10000;

            if ($paymentAmount >= self::PAYMENT_MINIMUM_AMOUNT) {
                return $paymentAmount;
            }
        }

        return 0;
    }
}
