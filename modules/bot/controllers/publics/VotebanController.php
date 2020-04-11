<?php

namespace app\modules\bot\controllers\publics;

use app\modules\bot\components\response\commands\SendMessageCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;
use app\modules\bot\components\Controller as Controller;

/**
 * Class HelloController
 *
 * @package app\controllers\bot
 */
class VotebanController extends Controller
{
    /**
     * @return array 
     */
    public function actionIndex()
    {
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index')
            ),
        ];
    }
}
