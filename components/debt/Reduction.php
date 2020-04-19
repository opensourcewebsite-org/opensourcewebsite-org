<?php

namespace app\components\debt;

use app\models\Debt;
use app\models\DebtBalance;
use app\models\queries\DebtBalanceQuery;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\db\Transaction;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

class Reduction extends Component
{
    public $printConsoleLog = false;
    /** @var callable|null */
    public $logger;

    /**
     * @throws \Throwable
     */
    public function run(): void
    {
        while ($firstChainMember = $this->findDebtBalanceFirstMember()) {
            $this->log('--- Starting search Circled Chain ---');

            $circledChain = $this->findCircledChain($firstChainMember->from_user_id, [$firstChainMember]);

            if ($circledChain) {
                $chainMembers = $this->listChainAsArray($circledChain);
                $function     = $this->reduceCircledChainAmount($chainMembers);
            } else {
                $function     = $this->cantReduceBalance($firstChainMember);
            }

            Yii::$app->db->transaction($function, Transaction::READ_COMMITTED);
        }
    }

    private function findDebtBalanceFirstMember(): ?DebtBalance
    {
        return DebtBalance::find()
            ->notResolved()
            ->orderBy('debt_balance.processed_at')
            ->limit(1)
            ->one();
    }

    /**
     * We need to find circled chain of balances. Example of minimal chain:
     *      from_user_id    to_user_id
     *          1               2           # first  chain member
     *          2               3           # middle chain member
     *          3               1           # last   chain member
     * Minimal circled chain length - 3 balances.
     * Each circled chain has 1 "first", 1 "last" and 1_or_more "middle" members.
     *
     * @param int           $firstFromUID
     * @param DebtBalance[] $chainMembers
     *
     * @return DebtBalance|null
     */
    private function findCircledChain($firstFromUID, array $chainMembers, int $level = 0): ?DebtBalance
    {
        $chainsWithMiddleMember = [];
        foreach ($chainMembers as $chainMember) {
            $this->log("$level. " . implode(':', $chainMember->primaryKey), [], true);

            $middleChainMembers = $this->findBalanceChains($firstFromUID, $chainMember);

            if (empty($middleChainMembers)) {
                $this->log('    dead end fork', [], true);
                continue; //if $chainMember has no "middle" members - it is dead end chain. It cannot has "last" member
            }
            $chainsWithMiddleMember[] = $middleChainMembers;

            $circledChain = $this->getCircledChain($middleChainMembers);

            if ($circledChain) {
                return $circledChain;
            }
        }

        if (empty($chainsWithMiddleMember)) {
            return null;
        }

        $chainsWithMiddleMember = array_merge(...$chainsWithMiddleMember);

        //try to go deeper - for each "middle" member find next step of chain members - until we find circled chain
        // or till we will try all possible variants
        return $this->findCircledChain($firstFromUID, $chainsWithMiddleMember, ++$level);
    }

    /**
     * @param int         $firstFromUID
     * @param DebtBalance $chainMember
     *
     * @return DebtBalance[]
     */
    private function findBalanceChains($firstFromUID, DebtBalance $chainMember): array
    {
        return $chainMember->getChainMembers()
            ->joinWith([
                'chainMembers' => function (DebtBalanceQuery $query) use ($firstFromUID) {
                    $query->alias('chainMembersLast')
                        ->andOnCondition(
                            '{{chainMembersLast}}.to_user_id = :first_fromUID',
                            [':first_fromUID' => $firstFromUID]
                        )
                        ->amountNotEmpty('chainMembersLast');
                }
            ])
            ->balances($this->getPreviousMembers($chainMember), 'NOT IN') //exclude previous to avoid continuous loop
            ->amountNotEmpty()
            ->all();
    }

    /**
     * @param DebtBalance[] $middleChainMembers
     *
     * @return DebtBalance|null if middleMember has any lastMember - this chain is circled
     */
    private function getCircledChain($middleChainMembers): ?DebtBalance
    {
        foreach ($middleChainMembers as $middle) {
            $this->log('    middle    ' . implode(':', $middle->primaryKey), [], true);

            if (!empty($middle->chainMembers)) {
                return $middle; // if it has at least one "last" chain member - then chain is circled
            }
        }

        return null;
    }

    /**
     * @return DebtBalance[]
     */
    private function listChainAsArray(DebtBalance $chainMember): array
    {
        $penultimateMember = clone $chainMember;

        $chainMembersAll   = $this->getPreviousMembers($chainMember, $minAmount);
        $chainMembersAll[] = $chainMember;
        $chainMembersAll[] = $this->getLastMember($penultimateMember, $minAmount);

        return $chainMembersAll;
    }

    /**
     * @param DebtBalance $chainMember
     * @param float       $minAmount
     *
     * @return DebtBalance[]
     */
    private function getPreviousMembers(DebtBalance $chainMember, float &$minAmount = null): array
    {
        /** @var DebtBalance[] $chainMembersAll */
        $chainMembersAll = [];
        $minAmount       = $chainMember->amount;

        //get all previous chain members
        while ($chainMember->isRelationPopulated('chainMemberParent')) {
            $chainMember       = $chainMember->chainMemberParent;
            $chainMembersAll[] = $chainMember;
            $minAmount         = ($chainMember->amount < $minAmount) ? $chainMember->amount : $minAmount;
        }

        //`array_reverse` is not necessary. Just for cleaner understanding on debugging
        return array_reverse($chainMembersAll);
    }

    /**
     * @param DebtBalance $penultimateMember
     * @param float       $minAmount
     *
     * @return DebtBalance  this method cannot return NULL!
     */
    private function getLastMember(DebtBalance $penultimateMember, float $minAmount): DebtBalance
    {
        /** @var DebtBalance|null $lastMemberBest */
        $lastMemberBest = null;

        foreach ($penultimateMember->chainMembers as $lastMember) {
            if ($lastMember->amount == $minAmount) {
                $lastMemberBest = $lastMember;
                break;
            }

            if (!$lastMemberBest || $lastMember->amount > $lastMemberBest->amount) {
                $lastMemberBest = $lastMember;
            }
        }

        $this->log('    last      ' . implode(':', $lastMemberBest->primaryKey), [], true);
        return $lastMemberBest;
    }

    /**
     * @param DebtBalance[] $chainMembers
     */
    private function reduceCircledChainAmount(array $chainMembers): callable
    {
        return function () use ($chainMembers) {
            $chainMembersRefreshed = DebtBalance::findAllForUpdate($chainMembers);

            $count = count($chainMembersRefreshed);
            if ($count != count($chainMembers)) {
                return; //some of balances we need, became zero or changed direction. This chain is not circled anymore
            }

            /** @var float $minAmount */
            $minAmount = min(ArrayHelper::getColumn($chainMembersRefreshed, 'amount'));
            $minAmount *= -1;
            if (!$minAmount) {
                return;
            }

            $group = microtime(true);
            foreach ($chainMembersRefreshed as $balance) {
                /** @var Debt $debt */
                $debt = Debt::factoryChangeBalance($balance, $minAmount);

                $debt->status = Debt::STATUS_CONFIRM;
                $debt->group  = $group;

                if (!$debt->save()) {
                    $message = "Unexpected error occurred: Fail to save Debt.\n";
                    $message .= 'Debt::$errors = ' . print_r($debt->errors, true);
                    throw new Exception($message);
                }
            }

            $chainLog = [];
            foreach ($chainMembers as $balance) {
                $chainLog[] = implode(':', $balance->primaryKey);
            }

            $this->log("amount=$minAmount   group=$group     " . implode(' -> ', $chainLog), [Console::BG_GREEN], true);

            $message = "Created chain. Amount=$debt->amount {$debt->currency->code}; Count of Debts=$count;";
            $message .= ' Count of Users=' . ($count + 1);
            $this->log($message);
        };
    }

    private function cantReduceBalance(DebtBalance $balance): callable
    {
        $this->log('Found 0 debt chains');

        return static function () use ($balance) {
            DebtBalance::unsetProcessedAt($balance);
        };
    }

    private function log($message, $format = [], $consoleOnly = false)
    {
        if ($this->logger && !$consoleOnly) {
            call_user_func($this->logger, $message, $format);
        }

        if (!$this->printConsoleLog) {
            return;
        }

        $message .= PHP_EOL;
        if (!empty($format)) {
            $message = Console::ansiFormat($message, $format);
        }

        Console::stdout($message);
    }
}
