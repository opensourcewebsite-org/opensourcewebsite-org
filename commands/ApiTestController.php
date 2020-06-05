<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\interfaces\ICronChained;
use app\modules\apiTesting\models\ApiTestServer;
use app\modules\apiTesting\services\RunnerScheduleManager;
use app\modules\apiTesting\services\RunnerService;
use app\modules\apiTesting\services\ServerService;
use yii\console\Controller;

/**
 * Class ApiTestController
 *
 * @package app\commands
 * @property RunnerService $runnerService
 * @property RunnerScheduleManager $scheduleManager
 * @property ServerService $serverService
 */
class ApiTestController extends Controller implements ICronChained {

    use ControllerLogTrait;

    private $runnerService;
    private $serverService;
    private $scheduleManager;

    private $serverVerifyCheckRate = 60;

    public function init()
    {
        parent::init();
        $this->runnerService = new RunnerService();
        $this->scheduleManager = new RunnerScheduleManager();
        $this->serverService = new ServerService();
    }

    public function runner() {
        $this->runnerService->runActualQueue();
    }

    public function verifyServers() {
        foreach (ApiTestServer::find()->unverified()->all() as $server) {
            if((($server->txt_checked_at + $this->serverVerifyCheckRate) >= time()) || $server->txt_checked_at == null) {
                $this->serverService->checkTxtOnServerAndVerify($server);
            }
        }
    }

    public function scheduleToRunner() {
        $this->scheduleManager->addScheduledJobsToRunner();
    }

    public function actionIndex()
    {
        $this->output('Run api test schedule to runner');
        $this->scheduleToRunner();
        $this->output('Run api test verify servers');
        $this->verifyServers();
        $this->output('Run api test runner');
        $this->runner();
    }
}
