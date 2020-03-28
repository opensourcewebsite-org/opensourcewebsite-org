<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\response\commands\SendMessageCommand;
use app\modules\bot\components\Controller;

/**
 * Class RandomStringController
 *
 * @package app\modules\bot\controllers
 */
class RandomStringController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($message = '')
    {
        //TODO add flexible int $n (1-1024) from $message
        $n = 10;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $randomString
            ),
        ];
    }
}
