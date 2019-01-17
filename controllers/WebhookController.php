<?php

namespace app\controllers;

use app\models\BotHandler;
use app\models\SupportGroupBot;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

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

            $botInfo = $this->findModel($token);

            $botApi = new BotHandler($token);
            $botApi->token = $token;
            $botApi->chat_id = $postdata['message']['chat']['id'];
            $botApi->language = $postdata['message']['from']['language_code'];
            $botApi->is_bot = $postdata['message']['from']['is_bot'];
            $botApi->command = $postdata['message']['text'];
            $botApi->support_group_id = $botInfo->support_group_id;
            $botApi->bot_id = $botInfo->id;
            $botApi->user_id = $postdata['message']['from']['id'];
            $botApi->user_name = $postdata['message']['from']['username'];

            # For Test in my country;
             $botApi->setProxy('156.67.84.75:60145');

            if ($botApi->is_bot) {
                return false;
            }

            $botApi->saveClientInfo();

            # check if it's command
            if (substr($botApi->command, 0, 1) != '/') {
                return false;
            }

            if ($botApi->executeLangCommand()) {
                return true;
            }

            return $botApi->executeCommand();
        }

        // \Yii::warning($postdata);

        return false;
    }

    /**
     * Finds the SupportGroupBot model.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param string $token
     *
     * @return SupportGroupBot the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($token)
    {
        if (($model = SupportGroupBot::findOne(['token' => $token])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
