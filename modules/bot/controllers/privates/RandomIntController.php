<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\components\response\SendMessageCommand;

/**
 * Class RandomIntController
 *
 * @package app\modules\bot\controllers
 */
class RandomIntController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex($message = '')
    {
        //TODO add flexible int min and max from $message
        $randomInt = random_int(1, 10);

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $randomInt
            ),
        ];
    }
}
