<?php

namespace app\commands;

use app\modules\bot\models\BotChatCaptcha;
use Yii;
use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\commands\traits\ControllerLogTrait;
use app\modules\bot\models\Bot;

/**
 * Class BotController
 *
 * @package app\commands
 */
class BotController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    const INTERVAL = 60; // seconds

    public function actionIndex()
    {
        $bots = Bot::findAll(['status' => Bot::BOT_STATUS_ENABLED]);

        if (isset($bots)) {
            foreach ($bots as $bot) {
                $botApi = new \TelegramBot\Api\BotApi($bot->token);

                $usersToBan = BotChatCaptcha::find()
                    ->select('bot_chat_captcha.*,bot_chat.chat_id as chat_id')
                    ->with('chat')
                    ->leftJoin('bot_chat', 'bot_chat_captcha.chat_id = bot_chat.id')
                    ->leftJoin('bot', 'bot_chat.bot_id = bot.id')
                    ->where(['<', 'sent_at', time() - self::INTERVAL])
                    ->andFilterWhere(['bot.id' => $bot->id])->all();

                if (isset($usersToBan)) {
                    try {
                        foreach ($usersToBan as $record) {
                            BotChatCaptcha::deleteAll([
                                'chat_id' => $record->chat_id,
                                'provider_user_id' => $record->provider_user_id
                            ]);

                            $botApi->deleteMessage($record->chat_id, $record->captcha_message_id);
                            $botApi->kickChatMember($record->chat_id, $record->provider_user_id);
                            $this->output("Kicked user {$record->provider_user_id} from chat {$record->chat->chat_id}");
                        }
                    } catch (\Throwable $t) {
                        $this->output("Error {$t->getMessage()} while kicking unverified users");
                        return false;
                    }
                }
            }
        }

        return true;
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
            if (isset(Yii::$app->params['telegramProxy'])) {
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
