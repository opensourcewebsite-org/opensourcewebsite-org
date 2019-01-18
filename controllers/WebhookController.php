<?php

namespace app\controllers;

use Yii;
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

        try {
            $postdata = file_get_contents('php://input');
            if ($postdata) {
                $postdata = json_decode($postdata, true);


                $botInfo = $this->findModel($token);

                $botApi = new BotHandler($token, $postdata);

                $botApi->support_group_id = $botInfo->support_group_id;
                $botApi->bot_id = $botInfo->id;

                # For Test in my country;
                if (isset(Yii::$app->params['telegramProxy'])) {
                    $botApi->setProxy(Yii::$app->params['telegramProxy']);
                }

                if ($botApi->getMessage()->getFrom()->isBot()) {
                    return false;
                }

                $botApi->saveClientInfo();

                # check if it's command
                if (substr($botApi->getMessage()->getText(), 0, 1) != '/') {
                    return false;
                }

                if ($botApi->executeLangCommand()) {
                    return true;
                }

                return $botApi->executeCommand();
            }

            // \Yii::warning($postdata);

            return false;
        } catch (\Exception $ex) {
            \Yii::error($ex->getMessage());

            return false;
        }
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
