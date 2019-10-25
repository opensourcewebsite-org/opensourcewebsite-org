<?php

namespace app\controllers;

use app\models\Bot;
use app\models\BotOutsideMessage;
use app\models\TelegramBotHandler;
use Yii;
use app\models\SupportGroupBotHandler;
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
        $result = false;
        try {
            $postdata = file_get_contents('php://input');
            if ($postdata) {
                $postdata = json_decode($postdata, true);

                $botInfo = Bot::findOne(['token' => $token]);
                if ($botInfo) {
                    $result = $this->handleBot($botInfo, $postdata);
                } elseif ($botInfo = SupportGroupBot::findOne(['token' => $token])) {
                    $result = $this->handleSupportGroupBot($botInfo, $postdata);
                } else {
                    throw new NotFoundHttpException('The requested page does not exist.');
                }
            }
        } catch (\Exception $ex) {
            \Yii::error($ex->getMessage());
        }

        return $result;
    }

    /**
     * @param SupportGroupBot $botInfo
     * @param array $postdata
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    protected function handleSupportGroupBot($botInfo, $postdata)
    {
        $botApi = new SupportGroupBotHandler($botInfo->token, $postdata);

        $botApi->support_group_id = $botInfo->support_group_id;
        $botApi->bot_id = $botInfo->id;

        if (isset(Yii::$app->params['telegramProxy'])) {
            $botApi->setProxy(Yii::$app->params['telegramProxy']);
        }

        if (!$botApi->getMessage() || $botApi->getMessage()->getFrom()->isBot()) {
            return false;
        }

        $botApi->bot_client_id = $botApi->saveClientInfo();

        # check if it's command
        if (substr(trim($botApi->getMessage()->getText()), 0, 1) != '/') {
            $botApi->type = 1;
            $botApi->saveOutsideMessage();
            $botApi->executeExchangeRateCommand();
            $botApi->executeLangCommand(false);

            return true;
        }

        $botApi->type = 2;
        $botApi->saveOutsideMessage();

        if ($botApi->executeLangCommand()) {
            return true;
        }

        return $botApi->executeCommand();
    }

    /**
     * @param Bot $botInfo
     * @param array $postdata
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    protected function handleBot($botInfo, $postdata)
    {
        $result = false;
        $botApi = new TelegramBotHandler($botInfo->token, $postdata);
        $botApi->bot_id = $botInfo->id;

        if (isset(Yii::$app->params['telegramProxy'])) {
            $botApi->setProxy(Yii::$app->params['telegramProxy']);
        }

        if ($botApi->getMessage() && !$botApi->getMessage()->getFrom()->isBot()) {
            $isCommand = (substr(trim($botApi->getMessage()->getText()), 0, 1) === '/');

            $botApi->bot_client_id = $botApi->saveClientInfo();
            $botApi->type = $isCommand ? BotOutsideMessage::TYPE_COMMAND
                : BotOutsideMessage::TYPE_ORDINARY_TEXT;

            if ($botApi->saveOutsideMessage()) {
                $result = $botApi->dispatchCommand();
            }
        }

        return $result;
    }
}
