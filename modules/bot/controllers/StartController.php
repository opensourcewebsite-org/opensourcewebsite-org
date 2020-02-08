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

    	$text = $this->render('index');

        return [
        	new SendMessageCommandSender(
        		new SendMessageCommand([
        			'chatId' => $update->getMessage()->getChat()->getId(),
        			'parseMode' => 'html',
        			'text' => $this->prepareText($text),
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
