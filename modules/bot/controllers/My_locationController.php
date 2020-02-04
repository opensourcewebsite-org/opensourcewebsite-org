<?php

namespace app\modules\bot\controllers;

use app\modules\bot\components\CommandController as Controller;
use app\modules\bot\components\BotClient;

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
    	

    	$userId = \Yii::$app->requestMessage->getChat()->getId();
    	$botClient = \Yii::$app->botClient->getModel();	

    	\Yii::$app->responseMessage->setKeyboard(new \TelegramBot\Api\Types\ReplyKeyboardMarkup([
    		[
    			[
    				'text' => \Yii::t('bot', 'Send Location'),
    				'request_location' => true
    			]
    		]
    	]));

        return $this->render('index', [
        	'longtitude' => $botClient->location_lon,
        	'latitude' => $botClient->location_lat,
        	'lastUpdate' => $botClient->location_at,
        ]);
    }
}
