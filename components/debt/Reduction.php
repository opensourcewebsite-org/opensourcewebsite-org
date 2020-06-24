<?php

namespace app\components\debt;

use app\commands\DebtController;
use app\components\helpers\DebtHelper;
use app\helpers\Number;
use app\models\Debt;
use app\models\DebtBalance;
use app\models\queries\DebtBalanceQuery;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\console\ExitCode;
use yii\db\Transaction;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

class Reduction extends Component
{
    /** @var int|null NULL - mean unlimited. Set this value empirically. */
    private const BREAK_LEVEL = 1000;
    /** @var float as percent: "0.9" mean 90%. It will break loop, if this limit will be reached */
    private const MEMORY_USAGE_LIMIT = 0.9;

    public array $debug = [
        'logConsole' => false,
        'DebtBalanceCondition' => null,
        //short format more handy to analyze logs. But if you are not familiar with this system yet - set FALSE
        'logChainShort' => false,
    ];
    /** @var callable|null */
    public $logger;

    /**
     * @throws \Throwable
     */
    public function run(): void
    {
        while ($balanceChainMemberFirst = $this->findDebtBalanceFirstMember()) {
            $this->log('--- Starting search Circled Chain ---');

            $balanceChainMemberCircled = $this->findCircledChain($balanceChainMemberFirst->from_user_id, [$balanceChainMemberFirst]);

            if ($balanceChainMemberCircled) {
                $function = $this->reduceCircledChainAmount($balanceChainMemberCircled);
            } else {
                $function = $this->cantReduceBalance($balanceChainMemberFirst);
            }

            Yii::$app->db->transaction($function, Transaction::READ_COMMITTED);
        }
    }

    private function findDebtBalanceFirstMember(): ?DebtBalance
    {
        $query = DebtBalance::find();

        if ($this->debug['DebtBalanceCondition']) {
            $query->andWhere($this->debug['DebtBalanceCondition']);
        } else {
            $query->canBeReduced(true);
        }

        $model = $query->limit(1)->one();

        if (!$model && $this->isDebugMode()) {
            $this->log('This balance is not available anymore', [Console::BG_GREY], true);
        }

        return $model;
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
     * @param DebtBalance[] $balanceChainMembers
     *
     * @return DebtBalance|null
     */
    private function findCircledChain($firstFromUID, array $balanceChainMembers, int $level = 0): ?DebtBalance
    {
        $chainsWithMiddleMemberBalance = [];
        foreach ($balanceChainMembers as $balanceChainMember) {
            $this->logChain($balanceChainMember, $level);
            $middleChainMembers = $this->findBalanceChains($firstFromUID, $balanceChainMember);

            if (empty($middleChainMembers)) {
                $this->log('    dead end fork', [], true);
                continue; //if $balanceChainMember has no "middle" members - it is dead end chain. It cannot has "last" member

                //REVIEW: maybe it is possible to use somehow members of dead-end chain, and use them as `NOT IN`
                // condition in self::findBalanceChains(). I'm not sure. But this guess came to me, while analyzing
                // huge debug logs (when $level >= 7)
            }
            $chainsWithMiddleMemberBalance[] = $middleChainMembers;

            $balanceCircledMember = $this->getCircledChain($middleChainMembers, $level);

            if ($balanceCircledMember) {
                return $balanceCircledMember;
            }
        }

        $breakLevel = $this->breakLevel($level, $balanceChainMembers[0]);
        if (empty($chainsWithMiddleMemberBalance) || $breakLevel || !$this->validateMemoryLimit()) {
            return null;
        }

        $chainsWithMiddleMemberBalance = array_merge(...$chainsWithMiddleMemberBalance);

        //try to go deeper - for each "middle" member find next step of chain members - until we find circled chain
        // or till we will try all possible variants
        return $this->findCircledChain($firstFromUID, $chainsWithMiddleMemberBalance, ++$level);
    }

    /**
     * @param int         $firstFromUID
     * @param DebtBalance $balanceChainMember
     *
     * @return DebtBalance[]
     */
    private function findBalanceChains($firstFromUID, DebtBalance $balanceChainMember): array
    {
        $previousBalanceMembers = $this->getPreviousMembers($balanceChainMember);
        $previousToUID = ArrayHelper::getColumn($previousBalanceMembers, 'to_user_id');

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
            ->balances($previousMembers, 'NOT IN') //exclude previous to avoid continuous loop
            ->userTo($previousToUID, 'NOT IN')     //exclude previous to optimize
            ->all();
    }

    /**
     * @param DebtBalance[] $middleBalanceChainMembers
     *
     * @return DebtBalance|null if middleMember has any lastMember - this chain is circled
     */
    private function getCircledChain(array $middleBalanceChainMembers, int $level): ?DebtBalance
    {
        ++$level;

        foreach ($middleBalanceChainMembers as $middleBalance) {
            $pk = implode(':', $middleBalance->primaryKey);
            $this->log("    middle    $pk ($level)", [], true);

            if (!empty($middleBalance->chainMembers)) {
                return $middleBalance; // if it has at least one "last" chain member - then chain is circled
            }
        }

        return null;
    }

    /**
     * @return DebtBalance[]
     */
    private function listChainAsArray(DebtBalance $balanceChainMember): array
    {
        $penultimateBalanceMember = clone $balanceChainMember;

        $balanceChainMembersAll   = $this->getPreviousMembers($balanceChainMember, $minAmount);
        $balanceChainMembersAll[] = $balanceChainMember;
        $balanceChainMembersAll[] = $this->getLastMember($penultimateBalanceMember, $minAmount);

        return $balanceChainMembersAll;
    }

    /**
     * @param DebtBalance  $balance
     * @param string|float $minAmount
     *
     * @return DebtBalance[]
     */
    private function getPreviousMembers(DebtBalance $balance, &$minAmount = ''): array
    {
        /** @var DebtBalance[] $balanceChainMembers */
        $balanceChainMembers = [];
        $minAmount = $balance->amount;
        $scale = DebtHelper::getFloatScale();

        //get all previous chain members
        while ($balance->isRelationPopulated('chainMemberParent')) {
            $balance = $balance->chainMemberParent;
            $balanceChainMembers[] = $balance;
            $isLower = Number::isFloatLower($balance->amount, $minAmount, $scale);

            $minAmount = $isLower ? $balance->amount : $minAmount;
        }

        return array_reverse($balanceChainMembers);
    }

    /**
     * @param DebtBalance $penultimateBalanceMember
     * @param string      $minAmount
     *
     * @return DebtBalance  this method cannot return NULL!
     */
    private function getLastMember(DebtBalance $penultimateBalanceMember, $minAmount): DebtBalance
    {
        /** @var DebtBalance|null $lastMemberBestBalance */
        $lastMemberBestBalance = null;
        $scale = DebtHelper::getFloatScale();

        foreach ($penultimateBalanceMember->chainMembers as $lastMemberBalance) {
            if (Number::isFloatEqual($lastMemberBalance->amount, $minAmount, $scale)) {
                $lastMemberBestBalance = $lastMemberBalance;
                break;
            }

            if (!$lastMemberBestBalance || Number::isFloatGreater($lastMemberBalance->amount, $lastMemberBestBalance->amount, $scale)) {
                $lastMemberBestBalance = $lastMemberBalance;
            }
        }

        $this->log('    last      ' . implode(':', $lastMemberBestBalance->primaryKey), [], true);
        return $lastMemberBestBalance;
    }

    private function reduceCircledChainAmount(DebtBalance $balanceChainMemberCircled): callable
    {
        return function () use ($balanceChainMemberCircled) {
            $balanceChainMembers = $this->listChainAsArray($balanceChainMemberCircled);
            $balanceChainMembersRefreshed = DebtBalance::findAllForUpdate($balanceChainMembers);

            $count = count($balanceChainMembersRefreshed);
            if ($count != count($balanceChainMembers)) {
                return; //some of balances we need, became zero or changed direction. This chain is not circled anymore
            }

            /** @var string $minAmount */
            $minAmount = min(ArrayHelper::getColumn($balanceChainMembersRefreshed, 'amount'));
            $scale = DebtHelper::getFloatScale();
            if (Number::isFloatEqual(0, $minAmount, $scale)) {
                return;
            }

            $group = Debt::generateGroup();
            foreach ($balanceChainMembersRefreshed as $balance) {
                $debt = Debt::factoryBySource($balance, -$minAmount, $group);

                if (!$debt->save()) {
                    $message = "Unexpected error occurred: Fail to save Debt.\n";
                    $message .= 'Debt::$errors = ' . print_r($debt->errors, true);
                    throw new Exception($message);
                }
            }

            $chainLog = [];
            foreach ($balanceChainMembers as $balance) {
                $chainLog[] = implode(':', $balance->primaryKey);
            }

            $this->log("amount=-$minAmount   group=$group     " . implode(' -> ', $chainLog), [Console::BG_GREEN], true);

            $message = "Created chain. Amount=$debt->amount {$debt->currency->code}; Count of Debts=$count;";
            $message .= ' Count of Users=' . ($count + 1);
            $this->log($message);
        };
    }

    private function cantReduceBalance(DebtBalance $balance): callable
    {
        $this->log('Found 0 balance chains');

        if ($this->isDebugMode()) {
            exit(ExitCode::SOFTWARE); //to avoid continuous loop, if debugging balance has no circled chain
        }

        return static function () use ($balance) {
            DebtBalance::setReductionTryAt($balance);
        };
    }

    private function log($message, $format = [], $consoleOnly = false): void
    {
        if ($this->isDebugMode()) {
            $consoleOnly = true;
        }

        if ($this->logger && !$consoleOnly) {
            call_user_func($this->logger, $message, $format);
            return;
        }

        if (!$this->debug['logConsole']) {
            return;
        }

        $message .= PHP_EOL;
        if (!empty($format)) {
            $message = Console::ansiFormat($message, $format);
        }

        Console::stdout($message);
    }

    private function breakLevel(int $level, DebtBalance $balanceMember): bool
    {
        if ($level !== self::BREAK_LEVEL) {
            return false;
        }

        $firstBalance = $this->getPreviousMembers($balanceMember)[0];
        $condition = DebtController::formatConsoleArgument($firstBalance->primaryKey);

        $message1 = "Can't find circled chain - script reached BREAK_LEVEL limit.";
        $message1 .= $this->isDebugMode() ? '' : ' Balance will be marked as Reduced.';
        $message1 .= " If you sure it is not bug - increase Reduction::BREAK_LEVEL. Now: $level";
        $this->log($message1, [Console::FG_RED]);

        //cron_job_log.message has limit 255 chars. So we should split message.
        $message2 = "You can debug exactly this balance:\n";
        $message2 .= "run `yii debt --debug-reduction=$condition`\n";
        $message2 .= 'analyze console messages to find bug';
        $this->log($message2, [Console::FG_RED]);

        Yii::error("$message1\n$message2", 'debt\reduction');

        if ($this->isDebugMode()) {
            exit(ExitCode::SOFTWARE);
        }

        return true;
    }

    private function isDebugMode(): bool
    {
        return (bool)$this->debug['DebtBalanceCondition'];
    }

    private function logChain(DebtBalance $balance, int $level): void
    {
        $balanceChainMembersAll = $this->getPreviousMembers($balance);
        $balanceChainMembersAll[] = $balance;
        $list = [];

        foreach ($balanceChainMembersAll as $key => $balanceChainMember) {
            $isShort = $this->debug['logChainShort'] && ($key !== 0);
            $list[] = $isShort ? $balanceChainMember->to_user_id : implode(':', $balanceChainMember->primaryKey);
        }

        $this->log("$level. " . implode(' => ', $list), [], true);
    }

    private function validateMemoryLimit(): bool
    {
        $usage = memory_get_usage(true);
        $limit = Number::getMemoryLimit();

        if ($limit <= 0 || ($usage / $limit) < self::MEMORY_USAGE_LIMIT) {
            return true;
        }

        $message = "Can't find circled chain - script reached memory limit.";
        $message .= $this->isDebugMode() ? '' : ' Balance will be marked as Reduced.';
        $this->log($message, [Console::FG_RED]);
        Yii::error($message, 'debt\reduction');

        return false;
    }
}
