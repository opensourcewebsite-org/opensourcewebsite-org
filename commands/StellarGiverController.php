<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\StellarGiver;
use app\models\StellarOperator;
use yii\console\Controller;

/**
 * Class StellarGiverController
 *
 * @package app\commands
 */
class StellarGiverController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    // TODO send basic income to participants
    public function actionIndex()
    {
    }
}
