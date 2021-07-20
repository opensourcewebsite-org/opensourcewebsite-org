<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\StellarServer;
use DateTime;
use yii\console\Controller;

/**
 * Class StellarCroupierController
 *
 * @package app\commands
 */
class StellarCroupierController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    public const PRIZE_MEMO_TEXT = 'Winner Prize';

    public function actionIndex()
    {
        $this->sendGameProfits();
    }

    protected function sendGameProfits()
    {
        if ($stellarServer = new StellarServer()) {
        }
    }
}
