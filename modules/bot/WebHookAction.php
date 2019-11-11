<?php

namespace app\modules\bot;

use yii\base\Action;
use app\modules\bot\telegram\BotApiClient;
use app\modules\bot\models\Bot;
use Yii;

/**
 * Class WebHookAction
 *
 * @package app\modules\bot
 */
class WebHookAction extends Action
{

    /**
     * @param string $token
     *
     * @return bool
     */
    public function run($token = '')
    {
        $result = false;
        try {
            $postData = file_get_contents('php://input');
            if ($postData) {
                $postData = json_decode($postData, true);

                $botInfo = Bot::findOne(['token' => $token]);
                if ($botInfo) {
                    $result = $this->handleBot($botInfo, $postData);
                }
            }
        } catch (\Exception $ex) {
            \Yii::error($ex->getMessage());
        }

        return $result;
    }

    /**
     * @param $botInfo Bot
     * @param $postData array
     *
     * @return bool
     * @throws \TelegramBot\Api\Exception
     * @throws \TelegramBot\Api\InvalidArgumentException
     */
    protected function handleBot($botInfo, $postData)
    {
        $botApi = new BotApiClient($botInfo->token, $postData);
        $botApi->bot_id = $botInfo->id;

        if (isset(Yii::$app->params['telegramProxy'])) {
            $botApi->setProxy(Yii::$app->params['telegramProxy']);
        }

        /** @var Module $botModule */
        $botModule = Yii::$app->getModule('bot');
        $botModule->initBotComponents($botApi);

        return $botModule->dispatchRoute();

    }
}