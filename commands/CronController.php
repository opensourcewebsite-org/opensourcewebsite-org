<?php

namespace app\commands;

use Yii;
use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\CronJob;
use app\models\CronJobConsole;
use yii\console\Controller;
use app\components\CustomConsole;
use yii\console\Exception;

/**
 * CronController is a cron manager.
 * It runs other commands, that chained in single thread (should be run one by one).
 * Instance https://github.com/opensourcewebsite-org/osw-devops/blob/master/pillar/prod/supervisor.sls#L22
 *
 * @property array $map
 * @property bool $log use param --log to show logs
 */
class CronController extends Controller
{
    use ControllerLogTrait;

    const INTERVAL = 60; // seconds
    const PREFIX = 'app\commands\\';
    const POSTFIX = 'Controller';

    private $cronJobs;

    /**
     * Map of input data with cron jobs
     *
     * @var array
     */
    static protected $map = [
        'WikipediaParser',
        'WikinewsParser',
        'Debt',
        'AdMatches',
        'Bot'
    ];

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $model = new CronJobConsole();
        $model->setCronJobs(static::$map);
        $model->add();
        $model->clear();

        return parent::beforeAction($action);
    }

    /**
     * {@inheritdoc}
     */
    public function options($actionID)
    {
        return $this->optionsAppendLog(parent::options($actionID));
    }

    /**
     * Main starter of all scripts
     */
    public function actionIndex()
    {
        $this->outputLogState();
        $this->cronJobs = CronJobConsole::find()->all();

//$this->cronJobs = [];

        if (empty($this->cronJobs)) {
            throw new Exception('Cron jobs not found');
        }

        while (true) {
            $session = Yii::$app->security->generateRandomString();
            $this->output(
                "[OPEN] session id: $session",
                [CustomConsole::FG_BLACK, CustomConsole::BG_YELLOW, CustomConsole::BOLD]
            );

            /** @var CronJobConsole $script */
            foreach ($this->cronJobs as $script) {
                if ($script->status !== CronJobConsole::STATUS_ON) {
                    continue;
                }

                $job = static::PREFIX . $script->name . static::POSTFIX;

                $this->output(
                    "[STARTED] $script->name",
                    [CustomConsole::FG_GREEN, CustomConsole::BOLD]
                );

                /** @var ControllerLogTrait|CronChainedInterface $controller */
                $controller = new $job(Yii::$app->controller->id, Yii::$app);
                $controller->log = $this->log;
                $controller->actionIndex();

                CronJob::updateAll(['updated_at' => time()], ['name' => $script->name]);

                $this->output(
                    "[FINISHED] $script->name",
                    [CustomConsole::FG_GREEN, CustomConsole::BOLD]
                );
            }

            $this->output(
                "[CLOSED] session id: $session",
                [CustomConsole::FG_BLACK, CustomConsole::BG_YELLOW, CustomConsole::BOLD]
            );

            $this->output();

            sleep(static::INTERVAL);
        }
    }
}
