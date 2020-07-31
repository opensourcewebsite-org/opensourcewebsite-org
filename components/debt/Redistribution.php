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

    private $wasRedistributed = false;

    /**
     * @throws \Throwable
     */
    public function run(): void
    {
        while ($debtBalance = $this->findDebtBalanceToRedistribute()) {
            $this->log('--- Starting search Circled Chain ---', [], true);
            $this->wasRedistributed = false;

            $chainMemberFirstContact = $debtBalance->factoryContact(true);
            $validEnd = $this->tryRedistribute($debtBalance, [$chainMemberFirstContact], $debtBalance->amount);

            if ($validEnd) {
                $debtBalance->afterRedistribution();
            }

            if (!$this->wasRedistributed) {
                $this->log('Cannot redistribute.', [Console::BG_GREY], true);
            }
        }
    }

    /**
     * We should try to reduce DebtBalance.
     * If it can't - try to Redistribute.
     * @return DebtBalanceQuery
     */
    private function queryDebtBalanceToRedistribute(DebtBalance $debtBalance = null): DebtBalanceQuery
    {
        $pk = $debtBalance ? $debtBalance->getPrimaryKey(true) : null;

        return DebtBalance::find()
            ->where($pk)
            ->canBeRedistributed()
            ->limit(1);
    }

    private function findDebtBalanceToRedistribute(): ?DebtBalance
    {
        return $this->queryDebtBalanceToRedistribute()->one();
    }

    /**
     * "Debtor"    -> "Debt amount" ->    "Debt Receiver"
     * We should:
     * 1) find all Debt Receiver's contact links (ReceiverFriends).
     * 2) if some ReceiverFriend has contact link to Debtor - it is the circled chain - we can redistribute
     * 3) else [recursion] return to point 1) and do the same for each ReceiverFriend
     *
     * @param DebtBalance $debtBalance
     * @param Contact[] $contactChainMembers
     * @param string $amountToRedistribute
     * @param int $level
     *
     * @return bool FALSE - some data become invalid. Need retry. TRUE - process finished normally.
     * @throws \Throwable
     */
    private function tryRedistribute(DebtBalance $debtBalance, $contactChainMembers, $amountToRedistribute, int $level = 1): bool
    {
        $contactChainMembersMiddle = [];
        $scale = DebtHelper::getFloatScale();

        foreach ($contactChainMembers as $contactChainMember) {
            $contactsReceiver = $this->findDebtReceiverCandidatesRedistributeInto($debtBalance, $contactChainMember, $level);

            $ownLimitsChecked = false;
            foreach ($contactsReceiver as $contactReceiver) {
                if ($level===1 && !$ownLimitsChecked && $this->applyOwnLimits($debtBalance, $contactReceiver, $amountToRedistribute)) {
                    if (Number::isFloatEqual($amountToRedistribute, 0, $scale)) {
                        return true;
                    }
                    //Script may reach this IF only when $level==1 and only once.
                    $ownLimitsChecked = true;
                }

                if ($level === 1 || (string)$debtBalance->debtorUID() !== (string)$contactReceiver->link_user_id) {
                    $contactChainMembersMiddle[] = $contactReceiver;
                    continue;
                }

                $function = $this->redistributeChain($debtBalance, $contactReceiver, $amountToRedistribute);
                /** @var null|string $amountToRedistribute */
                $amountToRedistribute = Yii::$app->db->transaction($function, Transaction::READ_COMMITTED);

                if (!Number::isFloatEqual($amountToRedistribute, 0, $scale)) {
                    continue; //if desired amount was redistributed only partially - try to search additional chain
                }

                return ($amountToRedistribute !== null);
            }
        }

        if (empty($contactChainMembersMiddle)) {
            return true;
        }

        return $this->tryRedistribute($debtBalance, $contactChainMembersMiddle, $amountToRedistribute, ++$level);
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
        if ($level === 1) {
            //exclude $debtBalance->toContact
            $excludeLinkUID = [$debtBalance->from_user_id];
        } else {
            //exclude previous to avoid continuous loop and optimize
            $contactChainListPrevious = $this->listChainMembers($contact);
            $excludeLinkUID = ArrayHelper::getColumn($contactChainListPrevious, 'link_user_id');
        }

        return $contact->getChainMembers()
            ->userLinked($excludeLinkUID, 'NOT IN')
            ->canRedistributeInto($debtBalance, $level)
            ->orderBy('contact.debt_redistribution_priority')
            ->all();
    }

    private function applyOwnLimits(DebtBalance $debtBalance, Contact $contactReceiver, &$amount): bool
    {
        if (
            !$debtBalance->hasRedistributionConfig() ||
            $debtBalance->toContact->debt_redistribution_priority > $contactReceiver->debt_redistribution_priority
        ) {
            return false;
        }

        $debtRedistribution = $debtBalance->toDebtRedistribution;
        $scale = DebtHelper::getFloatScale();
        $isLimitGreaterE = Number::isFloatGreaterE($debtRedistribution->max_amount, $amount, $scale);

        if ($isLimitGreaterE || $debtRedistribution->isMaxAmountAny()) {
            //this DebtBalance have not reached DebtRedistribution limit of his own Contact yet.
            $amount = '0';
        } else {
            //we should redistribute only part of debt: amount above limit (max_amount)
            $amount = Number::floatSub($amount, $debtRedistribution->max_amount, $scale);
        }

        return true;
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
            $message = "Redistribution chain. Amount=$amountPossible {$debt->currency->code}; Count of Debts=$count;";
            $message .= ' Count of Users=' . ($count + 1);
            $this->log($message);

            return Number::floatSub($amountWanted, $amountPossible, DebtHelper::getFloatScale());
        };
    }

    /**
     * Extract all chain members from last member into array
     *
     * @param Contact $chainMemberLastContact
     *
     * @return Contact[]
     */
    private function listChainMembers(Contact $chainMemberLastContact): array
    {
        /** @var Contact[] $chainMembersAll */
        $chainMembersAll = [$chainMemberLastContact];

        //get all previous chain members
        while ($chainMemberLastContact->isRelationPopulated('chainMemberParent')) {
            $chainMemberLastContact = $chainMemberLastContact->chainMemberParent;
            $chainMembersAll[] = $chainMemberLastContact;
        }

        //First contact is fake - it is generated from $debtBalance and need only for first select.
        array_pop($chainMembersAll);

        //`array_reverse` is not necessary. Just for cleaner understanding on debugging
        return array_reverse($chainMembersAll);
    }

    /**
     * @throws OutdatedObjectException
     */
    private function lockDebtBalance(DebtBalance $debtBalance): DebtBalance
    {
        $query = $this->queryDebtBalanceToRedistribute($debtBalance)
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
