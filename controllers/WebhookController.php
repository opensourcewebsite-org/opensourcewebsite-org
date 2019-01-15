<?php

namespace app\controllers;

use TelegramBot\Api\BotApi;
use app\models\SupportGroupCommand;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * Class WebhookController
 * @package app\controllers
 */
class WebhookController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * @param string $token the bot token
     * @return mixed
     */
    public function actionTelegram($token = '')
    {
        $postdata = file_get_contents('php://input');
        if ($postdata) {
            $postdata = json_decode($postdata, true);

            $chatID = $postdata["message"]["chat"]["id"];
            $command = $postdata["message"]["text"];
            $language = $postdata["message"]["from"]["language_code"];
            $is_bot = $postdata["message"]["from"]["is_bot"];

            if ($is_bot) {
                return false;
            }

            $commands = SupportGroupCommand::find()
                ->where(['token' => $token])
                ->andWhere(['command' => $command])
                ->joinWith([
                    'supportGroupBot',
                    'supportGroupCommandTexts'
                ])
                ->one();

            $botApi = new BotApi($token);

            // For Test in my country;
            // $botApi->setProxy('156.67.84.75:60145');

            if (!$commands) {
                $botApi->sendMessage($chatID, 'command not existed');

                return false;
            }

            $getLanguage = ArrayHelper::map($commands->supportGroupCommandTexts, 'language_code', 'text');

            if (ArrayHelper::keyExists($language, $getLanguage)) {
                $output = $getLanguage[$language];

                $botApi->sendMessage($chatID, $output);

                return true;
            }

            // get first command from array;
            $output = $commands->supportGroupCommandTexts[0];

            $botApi->sendMessage($chatID, $output);

            return true;
        }

        return false;

        //\Yii::warning($postdata);
    }
}
