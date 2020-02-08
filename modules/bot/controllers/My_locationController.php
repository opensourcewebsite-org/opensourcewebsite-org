<?php

namespace app\modules\bot\controllers;

use app\modules\bot\components\BotClient;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use \app\modules\bot\components\response\SendLocationCommandSender;
use \app\modules\bot\components\response\commands\SendLocationCommand;
use Yii;

/**
 * Class My_locationController
 *
 * @package app\modules\bot\controllers
 */
class My_locationController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $botClient = $this->getBotClient();
    	$update = $this->getUpdate();

        return [
            new SendLocationCommandSender(
                new SendLocationCommand([
                    'chatId' => $update->getMessage()->getChat()->getId(),
                    'longitude' => $botClient->location_lon,
                    'latitude' => $botClient->location_lat,
                    'replyMarkup' => new ReplyKeyboardMarkup([
                        [
                            [
                                'text' => Yii::t('bot', 'Send Location'),
                                'request_location' => TRUE,
                            ],
                            [
                                'text' => '/help',
                            ]
                        ]
                    ], TRUE, TRUE),
                ])
            ),
        ];
    }
}
