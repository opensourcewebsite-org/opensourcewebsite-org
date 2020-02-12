<?php

namespace app\modules\bot\controllers;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\ReplyKeyboardManager;

/**
 * Class StartController
 *
 * @package app\controllers\bot
 */
class StartController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
    	$update = $this->getUpdate();

        ReplyKeyboardManager::getInstance()->addKeyboardButton(0, [
            'text' => "⚙️",
            ReplyKeyboardManager::REPLYKEYBOARDBUTTON_IS_CONSTANT => TRUE,
        ]);

        return [
        	new SendMessageCommand(
                $update->getMessage()->getChat()->getId(),
                $this->render('index'),
        		[
                    'parseMode' => $this->textFormat,
        		]
        	),
        ];
    }
}
