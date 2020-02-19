<?php

namespace app\commands;

use yii\console\Controller;
use app\modules\bot\models\Bot;

/**
 * Class BotController
 *
 * @package app\commands
 */
class BotController extends Controller
{
    /**
     * Enable all inactive bots
     *
     * @throws \TelegramBot\Api\Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionEnableAll()
    {
        /** @var null|Bot[] $bots */
        $bots = Bot::find()->where(['status' => Bot::BOT_STATUS_DISABLED])->all();

        if ($bots) {
            foreach ($bots as $bot) {
                if ($bot->setWebhook()) {
                    echo "The bot \"{$bot->name}\" has been enabled\n";
                }
            }
        } else {
            echo "No inactive bots found.\n";
        }
    }

	/**
     * Disable all active bots
     *
     * @throws \TelegramBot\Api\Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDisableAll()
    {
        /** @var null|Bot[] $bots */
        $bots = Bot::find()->where(['status' => Bot::BOT_STATUS_ENABLED])->all();

        if ($bots) {
            foreach ($bots as $bot) {
                if ($bot->deleteWebhook()) {
                    echo "The bot \"{$bot->name}\" has been disabled\n";
                }
            }
        } else {
            echo "No active bots found.\n";
        }
    }

    /**
     * Add new bot or update exist bot
     *
     * @param $token
     */
    public function actionAdd(string $token) : bool
    {
        if (!$bot = Bot::findOne(['token' => $token])) {
            $bot = new Bot();

            $botApi = new \TelegramBot\Api\BotApi($token);
            if (isset(\Yii::$app->params['telegramProxy'])) {
                $botApi->setProxy(Yii::$app->params['telegramProxy']);
            }
            $user = $botApi->getMe();

            $bot->name = $user->getUsername();
            $bot->token = $token;
            $bot->status = 0;

            if ($bot->save()) {
                echo "The bot \"$bot->name\" has been successfully saved\n";

                return true;
            } else {
                echo current($bot->getFirstErrors()) . "\n";

                return false;
            }
        }

        echo 'Bot with the same token already exists';
        return false;
    }
}
