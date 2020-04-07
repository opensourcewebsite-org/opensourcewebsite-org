<?php
namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\interfaces\ICronChained;
use app\models\CronJob;
use app\models\CronJobConsole;
use yii\console\Controller;
use app\components\CustomConsole;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * CronController is a cron manager.
 * It run other commands, that chained in single thread (should be run one by one).
 *
 * @property array $map
 * @property bool $log
 */
class CronController extends Controller
{
    use ControllerLogTrait;

    const INTERVAL = 60;
    const PREFIX = 'app\commands\\';
    const POSTFIX = 'Controller';

    private $_cronJobs;

    /**
     * Map of input data
     *
     * @var array
     */
    static protected $map = [
        'WikipediaParser',
        'WikinewsParser',
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
        $this->_cronJobs = CronJobConsole::find()->all();

        if (empty($this->_cronJobs)) {
            throw new NotFoundHttpException;
        }

        while (true) {
            $session = Yii::$app->security->generateRandomString();
            $this->output(
                "[OPEN] session id: $session",
                [CustomConsole::FG_BLACK, CustomConsole::BG_YELLOW, CustomConsole::BOLD]
            );

            /** @var CronJobConsole $script */
            foreach ($this->_cronJobs as $script) {
                if ($script->status !== CronJobConsole::STATUS_ON) {
                    continue;
                }

                $job = static::PREFIX . $script->name . static::POSTFIX;

                $this->output(
                    "[PROCESS] Started script: $script->name",
                    [CustomConsole::FG_YELLOW, CustomConsole::BOLD]
                );

                /** @var ControllerLogTrait|ICronChained $controller */
                $controller = new $job(Yii::$app->controller->id, Yii::$app);
                $controller->log = $this->log;
                $controller->actionIndex();

                CronJob::updateAll(['updated_at' => time()], ['name' => $script->name]);

                $this->output(
                    "[OK]script $script->name finished ",
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
