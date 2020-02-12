<?php

namespace app\modules\bot\controllers;

use app\modules\bot\components\BotClient;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use \app\modules\bot\components\response\SendLocationCommand;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\ReplyKeyboardManager;
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

        ReplyKeyboardManager::getInstance()->addKeyboardButton(0, [
            'text' => $this->render('send-location'),
            'request_location' => TRUE,
        ]);

        if (isset($botClient->location_lon) && isset($botClient->location_lat))
        {
            return [
                new SendMessageCommand(
                    $update->getMessage()->getChat()->getId(),
                    $this->render('header'),
                    [
                        'parseMode' => $this->textFormat,
                    ]
                ),
                new SendLocationCommand(
                    $update->getMessage()->getChat()->getId(),
                    $botClient->location_lat,
                    $botClient->location_lon,
                ),
                new SendMessageCommand(
                    $update->getMessage()->getChat()->getId(),
                    $this->render('footer'),
                    [
                        'parseMode' => $this->textFormat,
                    ]
                ),
            ];
        }
        else
        {
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
            new SendMessageCommand(
                $update->getMessage()->getChat()->getId(),
                $this->render('update'),
                [
                    'parseMode' => $this->textFormat,
                ]
            ),
        ];
    }
}
