<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\components\debt\BalanceChecker;
use app\components\debt\Reduction;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\VarDumper;

class DebtController extends Controller
{
    use ControllerLogTrait;

    private const RESOLVE_INTERVAL = 2;

    /**
     * {@inheritdoc}
     */
    public function options($actionID)
    {
        return $this->optionsAppendLog(parent::options($actionID));
    }

    /**
     * Check that there are no data collision between DB tables `debt` and `debt_balance`.
     * Should be valid next formula: `debt_balance.amount = sumOfAllDebt(Credits) - sumOfAllDebt(Deposits)`
     *
     * @throws \yii\db\Exception
     */
    public function actionCheckBalance()
    {
        //in this action no sense to disable log.
        $this->log = true;
        $this->outputLogState();

        $errors = (new BalanceChecker)->run();

        if (null === $errors) {
            $this->output('There are no appropriate rows in DB table `debt`. Nothing to analyze.', [Console::BG_GREY]);
        } elseif (empty($errors)) {
            $this->output('SUCCESS: no bugs found.', [Console::FG_GREEN]);
        } else {
            $count = count($errors);
            $message = "ERROR: found $count data collisions!\n" . VarDumper::dumpAsString($errors);
            $this->output($message, [Console::FG_RED]);
        }
    }

    /**
     * @throws \Throwable
     */
    public function actionResolve()
    {
        while (true) {
            (new Reduction())->run();
            sleep(self::RESOLVE_INTERVAL);
            $this->stdout('.');
        }
    }

}
