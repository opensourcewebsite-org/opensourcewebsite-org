<?php
namespace app\commands;

use app\models\CronJob;
use yii\console\Controller;
use app\components\CustomConsole;
use Yii;
use yii\web\NotFoundHttpException;

/**
 *
 * @property \app\models\CronJob $cronJobs
 * @property bool $log
 */
class CronController extends Controller
{
    const INTERVAL = 60;
    const PREFIX = 'app\commands\\';
    const POSTFIX = 'Controller';

    public $cronJobs;
    public $log = false;

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $this->cronJobs = new CronJob();
        $this->cronJobs->add();
        $this->cronJobs->clear();

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
                true
            );
        }

        $this->cronJobs = $this->cronJobs->find()->all();

        if (empty($this->cronJobs)) {
            throw new NotFoundHttpException;
        }

        while (true) {
            $session = Yii::$app->security->generateRandomString();
            CustomConsole::output(
                CustomConsole::ansiFormat(
                    "[OPEN] session id: {$session}",
                    [CustomConsole::FG_BLACK, CustomConsole::BG_YELLOW, CustomConsole::BOLD]
                ),
                $this->log
            );

            foreach ($this->cronJobs as $script) {
                if ($script->status !== 1) {
                    continue;
                }

                $job = static::PREFIX . $script->name . static::POSTFIX;

                CustomConsole::output(
                    CustomConsole::ansiFormat(
                        "[PROCESS] Started script: {$script->name}",
                        [CustomConsole::FG_YELLOW, CustomConsole::BOLD]
                    ),
                    $this->log
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
                    $this->log
                );
            }

            CustomConsole::output(
                CustomConsole::ansiFormat(
                    "[CLOSED] session id: {$session}",
                    [CustomConsole::FG_BLACK, CustomConsole::BG_YELLOW, CustomConsole::BOLD]
                ),
                $this->log
            );

            CustomConsole::output(
                '',
                $this->log
            );

            sleep(static::INTERVAL);
        }
    }
}
