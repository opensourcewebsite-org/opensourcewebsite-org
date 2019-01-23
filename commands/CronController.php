<?php
namespace app\commands;

use app\models\CronJob;
use app\models\CronJobConsole;
use yii\console\Controller;
use app\components\CustomConsole;
use Yii;
use yii\web\NotFoundHttpException;

/**
 *
 * @property array $map
 * @property bool $log
 */
class CronController extends Controller
{
    const INTERVAL = 60;
    const PREFIX = 'app\commands\\';
    const POSTFIX = 'Controller';

    public $log = false;

    private $_cronJobs;

    /**
     * Map of input data
     *
     * @var array
     */
    static protected $map = [
        'WikipediaParser',
    ];

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $this->_cronJobs = new CronJobConsole();
        $this->_cronJobs->setCronJobs(static::$map);
        $this->_cronJobs->add();
        $this->_cronJobs->clear();

        return parent::beforeAction($action);
    }

    /**
     * {@inheritdoc}
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'log',
        ]);
    }

    /**
     * Main starter of all scripts
     */
    public function actionIndex()
    {
        if (!$this->log) {
            CustomConsole::output(
                CustomConsole::ansiFormat(
                    'LOGS MUTED (user param --log)',
                    [CustomConsole::FG_BLACK, CustomConsole::BG_YELLOW, CustomConsole::BOLD]
                ),
                ['logs' => true]
            );
        }

        $this->_cronJobs = $this->_cronJobs->find()->all();

        if (empty($this->_cronJobs)) {
            throw new NotFoundHttpException;
        }

        while (true) {
            $session = Yii::$app->security->generateRandomString();
            CustomConsole::output(
                CustomConsole::ansiFormat(
                    "[OPEN] session id: {$session}",
                    [CustomConsole::FG_BLACK, CustomConsole::BG_YELLOW, CustomConsole::BOLD]
                ),
                ['logs' => $this->log]
            );

            foreach ($this->_cronJobs as $script) {
                if ($script->status !== 1) {
                    continue;
                }

                $job = static::PREFIX . $script->name . static::POSTFIX;

                CustomConsole::output(
                    CustomConsole::ansiFormat(
                        "[PROCESS] Started script: {$script->name}",
                        [CustomConsole::FG_YELLOW, CustomConsole::BOLD]
                    ),
                    ['logs' => $this->log]
                );

                $controller = new $job(Yii::$app->controller->id, Yii::$app);
                $controller->log = $this->log;
                $controller->actionIndex();

                CronJob::updateAll(['updated_at' => time()], ['name' => $script->name]);

                CustomConsole::output(
                    CustomConsole::ansiFormat(
                        "[OK]script {$script->name} finished ",
                        [CustomConsole::FG_GREEN, CustomConsole::BOLD]
                    ),
                    ['logs' => $this->log]
                );
            }

            CustomConsole::output(
                CustomConsole::ansiFormat(
                    "[CLOSED] session id: {$session}",
                    [CustomConsole::FG_BLACK, CustomConsole::BG_YELLOW, CustomConsole::BOLD]
                ),
                ['logs' => $this->log]
            );

            CustomConsole::output(
                '',
                ['logs' => $this->log]
            );

            sleep(static::INTERVAL);
        }
    }
}
