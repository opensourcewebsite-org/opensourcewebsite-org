<?php

namespace app\models;

use ZuluCrypto\StellarSdk\Model\Account;
use ZuluCrypto\StellarSdk\Model\Payment;
use DateInterval;
use DateTime;

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
    public const WEEKLY_PAYMENT_PERCENT = 2; // %
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
            $paymentDate = ($today->format('l') === self::INCOME_WEEK_DAY) ? $today->format('Y-m-d') : $nextWeekDay->format('Y-m-d');
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

        $this
            ->buildTransaction(self::getGiverPublicKey())
            ->setAccountData('next_payment_date', $nextPaymentDate)
            ->submit(self::getGiverPrivateKey());

        StellarGiverData::setNextPaymentDate($nextPaymentDate);
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
