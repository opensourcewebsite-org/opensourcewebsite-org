<?php
namespace app\commands;

use yii\console\Controller;
use yii\helpers\Console;
use Yii;

/**
 *
 * @property integer $groupId
 */
class CronController extends Controller
{
    public function actionIndex()
    {
        ini_set('xdebug.max_nesting_level', 1000);

        $fromDb = [
            'app\commands\HelloController',
            'app\commands\WikipediaParserController'
        ];

        while (true) {

            foreach($fromDb as $items){
                Console::output(Console::ansiFormat('Started script: #1', [Console::FG_YELLOW, Console::BOLD]));
                $controller = new $items(Yii::$app->controller->id, Yii::$app);
                $controller->actionIndex();
                sleep(2);
                Console::output(Console::ansiFormat('script: #1 finished session', [Console::FG_BLUE, Console::BOLD]));
            }

            sleep(5);
        }

    }
}