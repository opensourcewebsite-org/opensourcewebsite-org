<?php

namespace app\modules\bot\controllers;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use \app\modules\bot\components\response\SendMessageCommand;

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
        	new SendMessageCommand(
                $update->getMessage()->getChat()->getId(),
                $this->render('index'),
        		[
                    'parseMode' => $this->textFormat,
        			'replyMarkup' => new ReplyKeyboardMarkup([
        				[
                            [
                                'text' => "⚙️",
                            ]
        				]
        			], TRUE, TRUE),
        		]
        	),
        ];
    }
}
