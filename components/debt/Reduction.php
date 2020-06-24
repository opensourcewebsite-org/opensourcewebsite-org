<?php

namespace app\components\debt;

use app\commands\DebtController;
use app\helpers\Number;
use app\models\Debt;
use app\models\DebtBalance;
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

            $balanceCircledMembers = $this->findCircledChains($balanceChainMemberFirst->from_user_id, [$balanceChainMemberFirst]);

            if (empty($balanceCircledMembers)) {
                $this->cantReduceBalance($balanceChainMemberFirst);
                continue;
            }

            foreach ($balanceCircledMembers as $balanceCircledMember) {
                $function = $this->reduceCircledChain($balanceCircledMember);
                Yii::$app->db->transaction($function, Transaction::READ_COMMITTED);
            }
        }
    }

    private function findDebtBalanceFirstMember(): ?DebtBalance
    {
        $query = DebtBalance::find()->select(DebtBalance::primaryKey())->limit(1);

        if ($this->debug['DebtBalanceCondition']) {
            $query->andWhere($this->debug['DebtBalanceCondition']);
        } else {
            $query->canBeReduced(true);
        }

        $model = $query->one();

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
     * @return DebtBalance[]
     */
    private function findCircledChains($firstFromUID, array $balanceChainMembers, int $level = 0): array
    {
        $chainsWithMiddleMemberBalance = [];
        $circledBalanceMembers = [];

        foreach ($balanceChainMembers as $balanceChainMember) {
            $this->logChain($balanceChainMember, $level);
            $middleChainMembers = $this->findBalanceChains($balanceChainMember);

            if (empty($middleChainMembers)) {
                $this->log('    dead end fork', [], true);
                continue; //if $balanceChainMember has no "middle" members - it is dead end chain. It cannot has "last" member

                //REVIEW: maybe it is possible to use somehow members of dead-end chain, and use them as `NOT IN`
                // condition in self::findBalanceChains(). I'm not sure. But this guess came to me, while analyzing
                // huge debug logs (when $level >= 7)
            }
            $circledBalanceMember = $this->getCircledMember($middleChainMembers, $firstFromUID, $level);

            if ($circledBalanceMember) {
                $circledBalanceMembers[] = $circledBalanceMember;
            } else {
                $chainsWithMiddleMemberBalance[] = $middleChainMembers;
            }
        }

        if (!empty($circledBalanceMembers)) {
            return $circledBalanceMembers;
        }

        $breakLevel = $this->breakLevel($level, $balanceChainMembers[0]);
        if (empty($chainsWithMiddleMemberBalance) || $breakLevel || !$this->validateMemoryLimit()) {
            return [];
        }

        $chainsWithMiddleMemberBalance = array_merge(...$chainsWithMiddleMemberBalance);

        //try to go deeper - for each "middle" member find next step of chain members - until we find circled chain
        // or till we will try all possible variants
        return $this->findCircledChains($firstFromUID, $chainsWithMiddleMemberBalance, ++$level);
    }

    /**
     * @return DebtBalance[]
     */
    private function findBalanceChains($firstFromUID, DebtBalance $balanceChainMember): array
    {
        $previousBalanceMembers = $this->getPreviousMembers($balanceChainMember);
        $previousToUID = ArrayHelper::getColumn($previousBalanceMembers, 'to_user_id');

        return $balanceChainMember->getChainMembers()
            ->select(DebtBalance::primaryKey())
            ->userTo($previousToUID, 'NOT IN')     //exclude previous to avoid continuous loop and optimize
            ->all();
    }

    /**
     * @param DebtBalance[] $middleBalanceChainMembers
     */
    private function getCircledMember(array $middleBalanceChainMembers, string $firstFromUID, int $level): ?DebtBalance
    {
        ++$level;

        foreach ($middleBalanceChainMembers as $middleBalance) {
            $pk = implode(':', $middleBalance->primaryKey);
            $this->log("    middle    $pk ($level)", [], true);

            if ($firstFromUID === (string)$middleBalance->to_user_id) {
                return $middleBalance;
            }
        }

        return null;
    }

    /**
     * @param DebtBalance $balance
     * @param bool $previousOnly   FALSE - return all chain members. TRUE - only previous (i.e. without $balance)
     *
     * @return DebtBalance[]
     */
    private function listChainAsArray(DebtBalance $balance, $previousOnly = false): array
    {
        /** @var DebtBalance[] $balanceChainMembers */
        $balanceChainMembers = [];
        if (!$previousOnly) {
            $balanceChainMembers[] = $balance;
        }

        //get all previous chain members
        while ($balance->isRelationPopulated('chainMemberParent')) {
            $balance = $balance->chainMemberParent;
            $balanceChainMembers[] = $balance;
        }

        return array_reverse($balanceChainMembers);
    }

    private function reduceCircledChain(DebtBalance $balanceCircledMember): callable
    {
        return function () use ($balanceCircledMember) {
            $balanceChainMembersAll = $this->listChainAsArray($balanceCircledMember);
            $balanceChainMembersRefreshed = DebtBalance::findAllForUpdate($balanceChainMembersAll);

            $count = count($balanceChainMembersRefreshed);
            if ($count !== count($balanceChainMembersAll)) {
                return; //some of balances we need, became zero or changed direction. This chain is not circled anymore
            }

            $minAmount = min(ArrayHelper::getColumn($balanceChainMembersRefreshed, 'amount'));
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
            foreach ($balanceChainMembersAll as $balance) {
                $chainLog[] = implode(':', $balance->primaryKey);
            }

            $this->log("amount=-$minAmount   group=$group     " . implode(' -> ', $chainLog), [Console::BG_GREEN], true);

            $message = "Created chain. Amount=$debt->amount {$debt->currency->code}; Count of Debts=$count;";
            $message .= ' Count of Users=' . ($count + 1);
            $this->log($message);
        };
    }

    /**
     * @throws \Throwable
     */
    private function cantReduceBalance(DebtBalance $balance): void
    {
        $this->log('Found 0 balance chains');

        if ($this->isDebugMode()) {
            exit(ExitCode::SOFTWARE); //to avoid continuous loop, if debugging balance has no circled chain
        }

        $function = static function () use ($balance) {
            DebtBalance::setReductionTryAt($balance);
        };
        Yii::$app->db->transaction($function, Transaction::READ_COMMITTED);
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

        $firstBalance = $this->listChainAsArray($balanceMember)[0];
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
        $balanceChainMembersAll = $this->listChainAsArray($balance);
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
