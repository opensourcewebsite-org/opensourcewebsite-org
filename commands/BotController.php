<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\commands\traits\ControllerLogTrait;
use app\modules\bot\models\Bot;
use app\modules\bot\models\BotChatCaptcha;
use app\modules\bot\models\ChatSetting;
use yii\console\Exception;

/**
 * Class BotController
 *
 * @package app\commands
 */
class BotController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    public function actionIndex()
    {
        $this->removeUnverifiedUsers();
    }

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
                    echo 'The bot "' . $bot->name . '" has been enabled' . "\n";
                }
            }
        } else {
            echo 'No inactive bots found' ."\n";
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
                    echo 'The bot "' . $bot->name . '" has been disabled' . "\n";
                }
            }
        } else {
            echo 'No active bots found' . "\n";
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
            if (isset(Yii::$app->params['telegramProxy'])) {
                $botApi->setProxy(Yii::$app->params['telegramProxy']);
            }
            $user = $botApi->getMe();

            $bot->name = $user->getUsername();
            $bot->token = $token;
            $bot->status = 0;

            if ($bot->save()) {
                echo 'The bot "' . $bot->name . '" has been successfully saved' . "\n";

                return true;
            } else {
                echo current($bot->getFirstErrors()) . "\n";

                return false;
            }
        }

        echo 'Bot with the same token already exists';

        return false;
    }

    public function removeUnverifiedUsers()
    {
        $updatesCount = 0;

        $bots = Bot::findAll(['status' => Bot::BOT_STATUS_ENABLED]);

        if ($bots) {
            foreach ($bots as $bot) {
                $usersToBan = BotChatCaptcha::find()
                    ->select('bot_chat_captcha.*')
                    ->with('chat')
                    ->leftJoin('bot_chat', 'bot_chat_captcha.chat_id = bot_chat.id')
                    ->leftJoin('bot', 'bot_chat.bot_id = bot.id')
                    ->where(['<', 'sent_at', time() - ChatSetting::JOIN_CAPTCHA_RESPONSE_AWAIT])
                    ->andFilterWhere(['bot.id' => $this->id])
                    ->all();

                if (isset($usersToBan)) {
                    $botApi = new \TelegramBot\Api\BotApi($this->token);

                    foreach ($usersToBan as $record) {
                        BotChatCaptcha::deleteAll([
                            'chat_id' => $record->chat->chat_id,
                            'provider_user_id' => $record->chat->provider_user_id,
                        ]);

                        try {
                            $botApi->deleteMessage($record->chat_id, $record->captcha_message_id);
                        } catch (Exception $e) {
                            echo 'ERROR: BotChatCaptcha #' . $record->id . ' (deleteMessage): ' . $e->getMessage() . "\n";
                        }

                        try {
                            $botApi->kickChatMember($record->chat_id, $record->provider_user_id);
                        } catch (Exception $e) {
                            echo 'ERROR: BotChatCaptcha #' . $record->id . ' (kickChatMember): ' . $e->getMessage() . "\n";
                        }
                        $updatesCount++;
                    }
                }
            }
        }

        if ($updatesCount) {
            $this->output('Users kicked from telegram groups (Join Captcha): ' . $updatesCount);
        }

        return true;
    }
}
