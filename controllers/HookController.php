<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

class HookController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'roles' => ['@'],
                        'allow' => true,
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        try {
            $telegram = new \Longman\TelegramBot\Telegram(Yii::$app->params['api_key'], Yii::$app->params['bot_username']);
            $telegram->addCommandsPath(Yii::getAlias('@app/commands'));
            $telegram->handle();
        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            echo $e->getMessage();
        }
    }


    /**
     * @return string
     */
    public function actionSetHook()
    {
        try {
            $telegram = new \Longman\TelegramBot\Telegram(Yii::$app->params['api_key']);
            echo $telegram->setWebHook(Yii::$app->params['urlWebHook'] . '/site/hook');

        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            echo $e->getMessage();
        }
    }
}
