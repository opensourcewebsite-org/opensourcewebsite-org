<?php

namespace app\modules\bot\controllers;

use app\modules\bot\components\BotClient;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use \app\modules\bot\components\response\SendLocationCommandSender;
use \app\modules\bot\components\response\SendMessageCommandSender;
use \app\modules\bot\components\response\commands\SendLocationCommand;
use \app\modules\bot\components\response\commands\SendMessageCommand;
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

        if (isset($botClient->location_lon) && isset($botClient->location_lat))
        {
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
                                    'text' => '⚙️',
                                ]
                            ]
                        ], TRUE, TRUE),
                    ])
                ),
            ];
        }
        else
        {
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
                                    'text' => Yii::t('bot', 'Send Location'),
                                    'request_location' => TRUE,
                                ],
                                [
                                    'text' => '⚙️',
                                ]
                            ]
                        ], TRUE, TRUE),
                    ])
                ),
            ];
        }
    }

    public function actionUpdate()
    {
        $botClient = $this->getBotClient();
        $update = $this->getUpdate();

        if ($update->getMessage() && ($location = $update->getMessage()->getLocation()))
        {
            $botClient->setAttributes([
                'location_lon' => $location->getLongitude(),
                'location_lat' => $location->getLatitude(),
                'location_at' => time(),
            ]);
            $botClient->save(); 
        }

        $text = $this->render('update');

        return [
            new SendMessageCommandSender(
                new SendMessageCommand([
                    'chatId' => $update->getMessage()->getChat()->getId(),
                    'parseMode' => 'html',
                    'text' => $this->prepareText($text),
                    'replyMarkup' => new ReplyKeyboardMarkup([
                        [
                            [
                                'text' => '⚙️',
                            ]
                        ]
                    ], TRUE, TRUE),
                ])
            ),
        ];
    }
}
