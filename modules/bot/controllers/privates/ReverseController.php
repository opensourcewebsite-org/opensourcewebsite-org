<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\components\response\SendMessageCommand;

/**
 * Class ReverseController
 *
 * @package app\modules\bot\controllers
 */
class ReverseController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex($message = '')
    {
        //TODO add reverse for $$message

        if ($message) {
            return [
                new SendMessageCommand(
                    $this->getTelegramChat()->chat_id,
                    $message
                ),
            ];
        }
    }
}
