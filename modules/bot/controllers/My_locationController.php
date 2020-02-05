<?php

namespace app\modules\bot\controllers;

use app\modules\bot\components\BotClient;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

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
    	$botClient = $this->module->botClient;
        
        return [
            [
                'type' => 'location',
                'longtitude' => $botClient->location_lon,
                'latitude' => $botClient->location_lat,
                'replyMarkup' => new ReplyKeyboardMarkup([
                                [
                                    [
                                        'text' => \Yii::t('bot', 'Send Location'),
                                        'request_location' => true
                                    ]
                                ]
                            ], true, true),
            ]
        ];
    }
}
