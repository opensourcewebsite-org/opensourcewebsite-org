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
    private const BREAK_LEVEL = 7;

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
        $query = DebtBalance::find();

        if ($this->debug['DebtBalanceCondition']) {
            $query->andWhere($this->debug['DebtBalanceCondition'])->amountNotEmpty();
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
     * @param DebtBalance[] $chainMembers
     *
     * @return DebtBalance|null
     */
    private function findCircledChain($firstFromUID, array $chainMembers, int $level = 0): ?DebtBalance
    {
        $chainsWithMiddleMember = [];
        foreach ($chainMembers as $chainMember) {
            $this->logChain($chainMember, $level);
            $middleChainMembers = $this->findBalanceChains($firstFromUID, $chainMember);

            if (empty($middleChainMembers)) {
                $this->log('    dead end fork', [], true);
                continue; //if $chainMember has no "middle" members - it is dead end chain. It cannot has "last" member
            }
            $chainsWithMiddleMember[] = $middleChainMembers;

            $circledChain = $this->getCircledChain($middleChainMembers, $level);

            if ($circledChain) {
                return $circledChain;
            }
        }

        if (empty($chainsWithMiddleMember) || $this->breakLevel($level, $chainMembers[0])) {
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
    private function getCircledChain($middleChainMembers, int $level): ?DebtBalance
    {
        ++$level;

        foreach ($middleChainMembers as $middle) {
            $pk = implode(':', $middle->primaryKey);
            $this->log("    middle    $pk ($level)", [], true);

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
     * @param string       $minAmount
     *
     * @return DebtBalance[]
     */
    private function getPreviousMembers(DebtBalance $chainMember, &$minAmount = ''): array
    {
        /** @var DebtBalance[] $chainMembersAll */
        $chainMembersAll = [];
        $minAmount = $chainMember->amount;
        $scale = DebtHelper::getFloatScale();

        //get all previous chain members
        while ($chainMember->isRelationPopulated('chainMemberParent')) {
            $chainMember = $chainMember->chainMemberParent;
            $chainMembersAll[] = $chainMember;
            $isLower = Number::isFloatLower($chainMember->amount, $minAmount, $scale);

            $minAmount = $isLower ? $chainMember->amount : $minAmount;
        }

        return array_reverse($chainMembersAll);
    }

    /**
     * @param DebtBalance $penultimateMember
     * @param string      $minAmount
     *
     * @return DebtBalance  this method cannot return NULL!
     */
    private function getLastMember(DebtBalance $penultimateMember, $minAmount): DebtBalance
    {
        /** @var DebtBalance|null $lastMemberBest */
        $lastMemberBest = null;
        $scale = DebtHelper::getFloatScale();

        foreach ($penultimateMember->chainMembers as $lastMember) {
            if (Number::isFloatEqual($lastMember->amount, $minAmount, $scale)) {
                $lastMemberBest = $lastMember;
                break;
            }

            if (!$lastMemberBest || Number::isFloatGreater($lastMember->amount, $lastMemberBest->amount, $scale)) {
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

            /** @var string $minAmount */
            $minAmount = min(ArrayHelper::getColumn($chainMembersRefreshed, 'amount'));
            $scale = DebtHelper::getFloatScale();
            if (Number::isFloatEqual(0, $minAmount, $scale)) {
                return;
            }

            $group = Debt::generateGroup();
            foreach ($chainMembersRefreshed as $balance) {
                $debt = Debt::factoryBySource($balance, -$minAmount, $group);

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

            $this->log("amount=-$minAmount   group=$group     " . implode(' -> ', $chainLog), [Console::BG_GREEN], true);

            $message = "Created chain. Amount=$debt->amount {$debt->currency->code}; Count of Debts=$count;";
            $message .= ' Count of Users=' . ($count + 1);
            $this->log($message);
        };
    }

    private function cantReduceBalance(DebtBalance $balance): callable
    {
        $this->log('Found 0 balance chains');

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

        $message = "Can't find balance chains - script reached BREAK_LEVEL limit.";
        $message .= " If you sure it is not bug - increase Reduction::BREAK_LEVEL. Now: $level";
        $this->log($message);

        //cron_job_log.message has limit 255 chars. So we should split message.
        $message = "You can debug exactly this balance:\n";
        $message .= "run `yii debt --debug-reduction=$condition`\n";
        $message .= 'analyze console messages to find bug';
        $this->log($message);

        if ($this->isDebugMode()) {
            exit(ExitCode::SOFTWARE);
        }

        return true;
    }

    private function isDebugMode(): bool
    {
        return (bool)$this->debug['DebtBalanceCondition'];
    }

    private function logChain(DebtBalance $chainMember, int $level): void
    {
        $chain = $this->getPreviousMembers($chainMember);
        $chain[] = $chainMember;
        $list = [];

        foreach ($chain as $key => $balance) {
            $isShort = $this->debug['logChainShort'] && ($key !== 0);
            $list[] = $isShort ? $balance->to_user_id : implode(':', $balance->primaryKey);
        }

        $this->log("$level. " . implode(' => ', $list), [], true);
    }
}
