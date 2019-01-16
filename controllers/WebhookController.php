<?php

namespace app\controllers;

use app\models\BotHandler;
use app\models\SupportGroupCommand;
use yii\web\Controller;

/**
 * Class WebhookController
 *
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
     *
     * @return mixed
     */
    public function actionTelegram($token = '')
    {

        $postdata = file_get_contents('php://input');
        if ($postdata) {
            $postdata = json_decode($postdata, true);

            $botApi = new BotHandler($token);
            $botApi->chat_id = $postdata['message']['chat']['id'];
            $botApi->language = $postdata['message']['from']['language_code'];
            $botApi->is_bot = $postdata['message']['from']['is_bot'];
            $botApi->command = $postdata['message']['text'];

            # For Test in my country;
            $botApi->setProxy('156.67.84.75:60145');

            if ($botApi->is_bot) {
                return false;
            }

            # check if it's command
            if (substr($botApi->command, 0, 1) != '/') {
                return false;
            }

            $commands = SupportGroupCommand::find()
                ->where(['token' => $token])
                ->andWhere(['command' => $botApi->command])
                ->joinWith([
                    'supportGroupBot',
                    'supportGroupCommandTexts',
                ])
                ->one();

            if (!$commands) {
                $default = SupportGroupCommand::find()
                    ->where(['token' => $token])
                    ->andWhere(['is_default' => 1])
                    ->joinWith([
                        'supportGroupBot',
                        'supportGroupCommandTexts',
                    ])
                    ->one();

                # there is no default commands, nothing is returned
                if (!$default) {
                    return false;
                }

                return $botApi->generateResponse($default->supportGroupCommandTexts);
            }

            return $botApi->generateResponse($commands->supportGroupCommandTexts);
        }

        // \Yii::warning($postdata);

        return false;
    }
}
