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
    public function getNextPaymentDate(): DateTime
    {
        $paymentDate = $this->getAccountDataByKey(self::getGiverPublicKey(), 'next_payment_date');

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
            ->buildTransaction(self::getGiverPublicKey())
            ->setAccountData('next_payment_date', $nextPaymentDate->format('Y-m-d'))
            ->submit(self::getGiverPrivateKey());
    }

    public function getConfirmedUsers()
    {
        // TODO
        return;
    }

    public function getConfirmedUsersCount()
    {
        // TODO
        return 0;

        return $this->getConfirmedUsers()->count();
    }

    public function getPaymentAmount()
    {
        if ($this->getConfirmedUsersCount() && $this->getAvailableBalance()) {
            $paymentAmount = floor(($this->getAvailableBalance() / $this->getConfirmedUsersCount()) * 10000) / 10000;

            if ($paymentAmount >= self::PAYMENT_MINIMUM_AMOUNT) {
                return $paymentAmount;
            }
        }

        return 0;
    }
}
