<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\commands\traits\ControllerLogTrait;
use yii\console\Exception;

/**
 * Class StellarController
 *
 * @package app\commands
 */
class StellarController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    public function actionIndex()
    {
        $this->sendDepositProfits();
    }

    protected function sendDepositProfits()
    {
        // TODO
    }
}
