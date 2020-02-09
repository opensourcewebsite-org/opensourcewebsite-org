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
                new SendMessageCommandSender(
                    new SendMessageCommand([
                        'chatId' => $update->getMessage()->getChat()->getId(),
                        'parseMode' => $this->textFormat,
                        'text' => $this->render('header'),
                    ])
                ),
                new SendLocationCommandSender(
                    new SendLocationCommand([
                        'chatId' => $update->getMessage()->getChat()->getId(),
                        'longitude' => $botClient->location_lon,
                        'latitude' => $botClient->location_lat,
                    ])
                ),
                new SendMessageCommandSender(
                    new SendMessageCommand([
                        'chatId' => $update->getMessage()->getChat()->getId(),
                        'parseMode' => $this->textFormat,
                        'text' => $this->render('footer'),
                        'replyMarkup' => new ReplyKeyboardMarkup([
                            [
                                [
                                    'text' => $this->render('send-location'),
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
            return [
                new SendMessageCommandSender(
                    new SendMessageCommand([
                        'chatId' => $update->getMessage()->getChat()->getId(),
                        'parseMode' => $this->textFormat,
                        'text' => $this->render('index'),
                        'replyMarkup' => new ReplyKeyboardMarkup([
                            [
                                [
                                    'text' => $this->render('send-location'),
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

        return [
            new SendMessageCommandSender(
                new SendMessageCommand([
                    'chatId' => $update->getMessage()->getChat()->getId(),
                    'parseMode' => $this->textFormat,
                    'text' => $this->render('update'),
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
