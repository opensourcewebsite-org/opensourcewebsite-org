<?php

namespace app\components\debt;

use app\components\helpers\ArrayHelper;
use app\components\helpers\DebtHelper;
use app\helpers\Number;
use app\models\Contact;
use app\models\Debt;
use app\models\DebtBalance;
use app\models\DebtRedistribution;
use app\models\queries\DebtBalanceQuery;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\db\Transaction;
use yii\helpers\Console;

class Redistribution extends Component
{
    public $printConsoleLog = false;
    /** @var callable|null */
    public $logger;

    private $timestamp;
    private $wasRedistributed = false;

    /**
     * @throws \Throwable
     */
    public function run(): void
    {
        $this->timestamp = time();
        while ($debtBalance = $this->findCandidateToRedistribute()->one()) {
            $this->log('--- Starting search Circled Chain ---');
            $this->wasRedistributed = false;

            $chainMemberFirst = $debtBalance->factoryContact(true);
            $validEnd = $this->tryRedistribute($debtBalance, [$chainMemberFirst], $debtBalance->amount);

            if ($validEnd) {
                $debtBalance->afterRedistribution($this->timestamp);
            }

            if (!$this->wasRedistributed) {
                $this->log('Cannot redistribute.');
            }
        }
    }

    /**
     * We should try to reduce DebtBalance.
     * If it can't - try to Redistribute.
     * @return DebtBalanceQuery
     */
    private function findCandidateToRedistribute(DebtBalance $debtBalance = null): DebtBalanceQuery
    {
        $pk = $debtBalance ? $debtBalance->getPrimaryKey(true) : null;

        return DebtBalance::find()
            ->where($pk)
            ->canBeRedistributed($this->timestamp)
            ->limit(1);
    }

    /**
     * "Debtor"    -> "Debt amount" ->    "Debt Receiver"
     * We should:
     * 1) find all Debt Receiver's contact links (ReceiverFriends).
     * 2) if some ReceiverFriend has contact link to Debtor - it is the circled chain - we can redistribute
     * 3) else [recursion] return to point 1) and do the same for each ReceiverFriend
     *
     * @param DebtBalance $debtBalance
     * @param Contact[] $chainMembers
     * @param string $amountToRedistribute
     * @param int $level
     *
     * @return bool FALSE - some data become invalid. Need retry. TRUE - process finished normally.
     * @throws \Throwable
     */
    private function tryRedistribute(DebtBalance $debtBalance, $chainMembers, $amountToRedistribute, int $level = 1): bool
    {
        $chainMemberMiddle = [];
        $scale = DebtHelper::getFloatScale();

        foreach ($chainMembers as $chainMember) {
            $debtReceiverContacts = $this->findDebtReceiverCandidatesRedistributeInto($debtBalance, $chainMember, $level);

            foreach ($debtReceiverContacts as $receiverContact) {
                if ($this->checkOwnLimits($debtBalance, $receiverContact, $amountToRedistribute)) {
                    //Script may reach this IF only when $level==1 and only once.
                    // In this IF you don't see any conditions to ensure it, because similar conditions implemented
                    // in method `findDebtReceiverCandidatesRedistributeInto()` via SQL using `$level`.
                    // So they will be redundant here.
                    if (Number::isFloatEqual($amountToRedistribute, 0, $scale)) {
                        return true;
                    }
                    continue;
                }

                $contactCircled = $this->findContactCircled($debtBalance, $receiverContact);
                if (!$contactCircled) {
                    $chainMemberMiddle[] = $receiverContact;
                    continue;
                }

                $function = $this->redistributeChain($debtBalance, $contactCircled, $amountToRedistribute);
                /** @var null|string $amountToRedistribute */
                $amountToRedistribute = Yii::$app->db->transaction($function, Transaction::READ_COMMITTED);

                if (!Number::isFloatEqual($amountToRedistribute, 0, $scale)) {
                    continue; //if desired amount was redistributed only partially - try to search additional chain
                }

                return ($amountToRedistribute !== null);
            }
        }

        if (empty($chainMemberMiddle)) {
            return true;
        }

        return $this->tryRedistribute($debtBalance, $chainMemberMiddle, $amountToRedistribute, ++$level);
    }

    /**
     * @param DebtBalance $debtBalance
     * @param Contact $contact
     * @param int $level  on first level we need to find own Contact related to DebtBalance. (to consider it's settings)
     *                    but on deeper levels we should exclude Contacts already in chain. (to avoid continuous loop)
     *
     * @return Contact[] list is already ordered by priority. First - is the highest. Last - is the lowest.
     */
    private function findDebtReceiverCandidatesRedistributeInto(
        DebtBalance $debtBalance,
        Contact $contact,
        int $level
    ): array
    {
        $chainList = ($level === 1) ? [] : $this->listChainMembers($contact);

        return $contact->getChainMembers()
            ->models($chainList, 'NOT IN', ['user_id', 'link_user_id'])
            ->canRedistributeInto($debtBalance->currency_id)
            ->orderBy('debt_redistribution_priority')
            ->all();
    }

    /**
     * if we reached to Contact which is related to $debtBalance
     *   then this contact is the most prioritized Contact now.
     *   So we should either break or decrease $amountToRedistribute.
     *
     * @param DebtBalance $debtBalance
     * @param Contact $receiverContact
     * @param string $amount
     *
     * @return bool
     */
    private function checkOwnLimits(DebtBalance $debtBalance, Contact $receiverContact, &$amount): bool
    {
        if (!$debtBalance->toContact || $debtBalance->toContact->id != $receiverContact->id) {
            return false;
        }

        $cfg = $receiverContact->debtRedistributionByDebtorCustom;
        $scale = DebtHelper::getFloatScale();

        if ($cfg->isMaxAmountAny() || Number::isFloatGreaterE($cfg->max_amount, $amount, $scale)) {
            //this DebtBalance have not reached DebtRedistribution limit of his own Contact yet.
            $amount = '0';
        } else {
            //we should redistribute only part of debt: amount above limit (max_amount)
            $amount = Number::floatSub($amount, $cfg->max_amount, $scale);
        }

        return true;
    }

    /**
     * is ReceiverContact has Contact linked to Debtor?
     *
     * @param DebtBalance $debtBalance
     * @param Contact $receiverContact
     *
     * @return Contact|null
     */
    private function findContactCircled(DebtBalance $debtBalance, Contact $receiverContact): ?Contact
    {
        return $receiverContact->getChainMembers()
            ->userLinked($debtBalance->debtorUID())
            ->canRedistributeInto($debtBalance->currency_id)
            ->one();
    }

    /**
     * it should run in transaction. It should SELECT FOR UPDATE $debtBalance & all chainMembers
     *   to verify that they are still valid. And to lock them before update.
     *   Possible result cases:
     *       1) all valid - create Debt chain. Reduce $amountToRedistribute.
     *       2) required attributes changed (not all) - break Redistribution of this $debtBalance.
     *           Script will automatically try again this balance if it still fit requirements of findCandidateToRedistribute()
     *
     * @param DebtBalance $debtBalance
     * @param Contact $contactCircled
     * @param string $amountWanted
     *
     * @return \Closure
     */
    private function redistributeChain(DebtBalance &$debtBalance, Contact $contactCircled, $amountWanted): callable
    {
        return function () use (&$debtBalance, $contactCircled, $amountWanted): ?string {
            $contacts = $this->listChainMembers($contactCircled);
            //we don't need First contact here. (generated from $debtBalance).
            // More convenient to use exactly $debtBalance to generate First Debt
            array_shift($contacts);
            $debtRedistributions = ArrayHelper::getColumn($contacts, 'debtRedistributionByDebtorCustom');
            $debtBalances = ArrayHelper::getColumn($debtRedistributions, 'debtBalanceDirectionBack');
            $debtBalances = array_filter($debtBalances, static function ($item) { return (bool)$item; });

            try {
                $debtBalance = $this->lockDebtBalance($debtBalance);
                $this->lockContacts($contacts);
                $debtRedistributionsFresh = $this->lockDebtRedistributions($debtRedistributions);
                $debtBalancesFresh = $this->lockDebtBalances($debtBalances);
                $amountPossible = $this->calcAmountToRedistribute(
                    $debtRedistributionsFresh,
                    $debtBalancesFresh,
                    $amountWanted
                );
            } catch (OutdatedObjectException $exception) {
                return null;
            }

            $debts = $this->buildDebts($debtBalance, $amountPossible, $debtRedistributionsFresh);

            foreach ($debts as $debt) {
                if (!$debt->save()) {
                    $message = "Unexpected error occurred: Fail to save Debt.\n";
                    $message .= 'Debt::$errors = ' . print_r($debt->errors, true);
                    throw new Exception($message);
                }
            }

            $this->wasRedistributed = true;

            $count = count($debts);
            $message = "Created chain. Amount=$amountPossible {$debt->currency->code}; Count of Debts=$count;";
            $message .= ' Count of Users=' . ($count + 1);
            $this->log($message);

            return Number::floatSub($amountWanted, $amountPossible, DebtHelper::getFloatScale());
        };
    }

    /**
     * Extract all chain members from last member into array
     *
     * @param Contact $chainMemberLast
     *
     * @return Contact[]
     */
    private function listChainMembers(Contact $chainMemberLast): array
    {
        /** @var Contact[] $chainMembersAll */
        $chainMembersAll = [$chainMemberLast];

        //get all previous chain members
        while ($chainMemberLast->isRelationPopulated('chainMemberParent')) {
            $chainMemberLast = $chainMemberLast->chainMemberParent;
            $chainMembersAll[] = $chainMemberLast;
        }

        //`array_reverse` is not necessary. Just for cleaner understanding on debugging
        return array_reverse($chainMembersAll);
    }

    /**
     * @throws OutdatedObjectException
     */
    private function lockDebtBalance(DebtBalance $debtBalance): DebtBalance
    {
        $query = $this->findCandidateToRedistribute($debtBalance)
            ->amount($debtBalance->amount);
        $debtBalanceFresh = DebtBalance::findOneForUpdate($query);

        if (!$debtBalanceFresh) {
            throw new OutdatedObjectException();
        }

        return $debtBalanceFresh;
    }

    /**
     * @param Contact[] $contacts
     *
     * @return Contact[]
     * @throws OutdatedObjectException
     */
    private function lockContacts($contacts): array
    {
        $contactsFresh = Contact::findAllForUpdate($contacts);

        if (count($contactsFresh) !== count($contacts)) {
            throw new OutdatedObjectException();
        }

        return $contactsFresh;
    }

    /**
     * @param DebtRedistribution[] $debtRedistributions
     *
     * @return DebtRedistribution[]
     * @throws OutdatedObjectException
     */
    private function lockDebtRedistributions($debtRedistributions): array
    {
        $attributes = DebtRedistribution::primaryKey();
        $attributes[] = 'max_amount';
        $query = DebtRedistribution::find()
            ->models($debtRedistributions, 'IN', $attributes)
            ->indexBy(static function (DebtRedistribution $model) { return self::stringifyUniqueKey($model); });
        $debtRedistributionsFresh = DebtRedistribution::findAllForUpdate($query);
        if (count($debtRedistributionsFresh) !== count($debtRedistributions)) {
            throw new OutdatedObjectException();
        }

        return $debtRedistributionsFresh;
    }

    /**
     * @param DebtBalance[] $debtBalances
     *
     * @return DebtBalance[]
     * @throws OutdatedObjectException
     */
    private function lockDebtBalances($debtBalances): array
    {
        $attributes = DebtBalance::primaryKey();
        $attributes[] = 'amount';
        $query = DebtBalance::find()
            ->models($debtBalances, 'IN', $attributes)
            ->indexBy(static function (DebtBalance $model) { return self::stringifyUniqueKey($model); });
        $debtBalancesFresh = DebtBalance::findAllForUpdate($query);
        if (count($debtBalancesFresh) !== count($debtBalances)) {
            throw new OutdatedObjectException();
        }

        return $debtBalancesFresh;
    }

    /**
     * @param DebtRedistribution[] $debtRedistributions
     * @param DebtBalance[] $debtBalances
     * @param string $amountWanted
     *
     * @return string
     * @throws OutdatedObjectException
     */
    private function calcAmountToRedistribute($debtRedistributions, $debtBalances, $amountWanted)
    {
        $maxAmount = $amountWanted;
        $scale = DebtHelper::getFloatScale();

        foreach ($debtRedistributions as $uniqueKey => $debtRedistribution) {
            if ($debtRedistribution->isMaxAmountAny()) {
                continue;
            }
            $balanceAmount = $debtBalances[$uniqueKey]->amount ?? 0;
            $limit = Number::floatSub($debtRedistribution->max_amount, $balanceAmount, $scale);
            if (Number::isFloatLower($limit, 0, $scale)) {
                throw new OutdatedObjectException();
            }
            if (Number::isFloatLower($limit, $maxAmount, $scale)) {
                $maxAmount = $limit;
            }
        }

        return $maxAmount;
    }

    /**
     * @param DebtBalance $debtBalance
     * @param string $amount
     * @param DebtRedistribution[] $debtRedistributions
     *
     * @return Debt[]
     */
    private function buildDebts(DebtBalance $debtBalance, $amount, $debtRedistributions): array
    {
        $group = Debt::generateGroup();

        /** @var Debt[] $debts */
        $debts = [];
        //this first Debt has (Debt::isUpdateProcessedFlag() == FALSE). It's ok.
        // We don't need it on amount decreasing if amount remain greater than 0
        $debts[] = Debt::factoryBySource($debtBalance, -$amount, $group);

        foreach ($debtRedistributions as $debtRedistribution) {
            $debts[] = Debt::factoryBySource($debtRedistribution, $amount, $group);
        }

        return $debts;
    }

    private function log($message, $format = [], $consoleOnly = false)
    {
        $message .= PHP_EOL;

        if ($this->logger && !$consoleOnly) {
            call_user_func($this->logger, $message, $format);
        }

        if (!$this->printConsoleLog) {
            return;
        }

        if (!empty($format)) {
            $message = Console::ansiFormat($message, $format);
        }

        Console::stdout($message);
    }

    /**
     * @param DebtBalance|DebtRedistribution $model
     *
     * @return string
     */
    public static function stringifyUniqueKey($model): string
    {
        return "$model->currency_id:{$model->debtorUID()}:{$model->debtReceiverUID()}";
    }
}
