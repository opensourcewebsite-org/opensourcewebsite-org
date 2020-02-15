<?php

namespace app\modules\bot\controllers\publics;

use \app\modules\bot\components\response\SendMessageCommand;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;
use app\modules\bot\components\Controller as Controller;

/**
 * Class HelloController
 *
 * @package app\controllers\bot
 */
class HelloController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index'),
                [
                    'parseMode' => $this->textFormat,
                ]
            ),
        ];
    }
}
