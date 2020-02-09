<?php

namespace app\modules\bot\controllers;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use \app\modules\bot\components\response\SendMessageCommandSender;
use \app\modules\bot\components\response\commands\SendMessageCommand;

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

        return [
        	new SendMessageCommandSender(
        		new SendMessageCommand([
        			'chatId' => $update->getMessage()->getChat()->getId(),
                    'parseMode' => $this->textFormat,
        			'text' => $this->render('index'),
        			'replyMarkup' => new ReplyKeyboardMarkup([
        				[
                            [
                                'text' => "⚙️",
                            ]
        				]
        			], TRUE, TRUE),
        		])
        	),
        ];
    }
}
