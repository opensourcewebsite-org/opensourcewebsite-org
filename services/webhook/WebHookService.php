<?php

namespace app\services;

use Yii;
use app\models\SupportGroupBotHandler;
use app\models\SupportGroupBot;

/**
 * Class WebhookController
 *
 * @package app\controllers
 */
class WebhookController extends Controller
{
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
            //if (!$botApi->getMessage()->isBotCommand()) {
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
}
