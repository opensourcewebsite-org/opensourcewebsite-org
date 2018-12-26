<?php
namespace app\commands;

use app\models\CronJob;
use yii\console\Controller;
use yii\helpers\Console;
use Yii;
use yii\web\NotFoundHttpException;

/**
 *
 * @property \app\models\CronJob $cronJobs
 * @property bool $log
 */
class CronController extends Controller
{
    const PREFIX = "app\\commands\\";
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
        ini_set('xdebug.max_nesting_level', 1000);

        $jobs = $this->cronJobs->find()->select('name')->column();

        if(empty($jobs)){
            throw new NotFoundHttpException;
        }

        while (true) {

            $session = Yii::$app->security->generateRandomString();
            Console::output(Console::ansiFormat("[START] session id: {$session}", [Console::FG_BLACK, Console::BG_YELLOW, Console::BOLD]));

            foreach($jobs as $script){

                $job = static::PREFIX  . $script . static::POSTFIX;

                Console::output(Console::ansiFormat("[PROCESS] Started script: {$script}", [Console::FG_YELLOW, Console::BOLD]));

                $controller = new $job(Yii::$app->controller->id, Yii::$app);
                $controller->log = $this->log;
                $controller->actionIndex();

                CronJob::updateAll(['updated_at' => time()], ['name' => $script]);

                Console::output(Console::ansiFormat("[OK]script {$script} finished ", [Console::FG_GREEN, Console::BOLD]));
            }

            Console::output(Console::ansiFormat("[FINISH] session id: {$session}", [Console::FG_BLACK, Console::BG_YELLOW, Console::BOLD]));

            Console::output();
            sleep(5);
        }
    }
}