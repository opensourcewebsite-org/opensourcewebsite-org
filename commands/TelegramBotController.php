<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\commands\traits\ControllerLogTrait;
use app\modules\bot\models\Bot;
use app\modules\bot\models\BotChatCaptcha;
use app\modules\bot\models\BotChatGreeting;
use app\modules\bot\models\ChatSetting;
use yii\console\Exception;

/**
 * Class TelegramBotController
 *
 * @package app\commands
 */
class TelegramBotController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    public function actionIndex()
    {
        $this->removeCaptchaMessages();
        //$this->removeGreetingMessages();
    }

    /**
     * Restart all bots
     *
     * @throws \TelegramBot\Api\Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionRestartAll()
    {
        $this->actionDisableAll();
        $this->actionEnableAll();
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
        $bots = Bot::find()
            ->where([
                'status' => Bot::BOT_STATUS_DISABLED,
                ])
            ->all();

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
        $bots = Bot::find()
            ->where([
                'status' => Bot::BOT_STATUS_ENABLED,
                ])
            ->all();

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
    public function actionAdd(string $token): bool
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

    public function removeCaptchaMessages()
    {
        $updatesCount = 0;

        $bots = Bot::findAll([
            'status' => Bot::BOT_STATUS_ENABLED,
        ]);

        if ($bots) {
            foreach ($bots as $bot) {
                $messagesToRemove = BotChatCaptcha::find()
                    ->where(['<', 'sent_at', time() - ChatSetting::JOIN_CAPTCHA_MESSAGE_LIFETIME])
                    ->joinWith('chat')
                    ->andWhere(['bot_chat.bot_id' => $bot->id])
                    ->all();

                if ($messagesToRemove) {
                    $botApi = new \TelegramBot\Api\BotApi($bot->token);

                    foreach ($messagesToRemove as $record) {
                        BotChatCaptcha::deleteAll([
                            'chat_id' => $record->chat_id,
                            'provider_user_id' => $record->provider_user_id,
                        ]);

                        try {
                            $botApi->deleteMessage($record->chat->chat_id, $record->captcha_message_id);
                        } catch (\Exception $e) {
                            echo 'ERROR: BotChatCaptcha #' . $record->id . ' (deleteMessage): ' . $e->getMessage() . "\n";
                        }

                        try {
                            $botApi->kickChatMember($record->chat->chat_id, $record->provider_user_id);
                        } catch (\Exception $e) {
                            echo 'ERROR: BotChatCaptcha #' . $record->id . ' (kickChatMember): ' . $e->getMessage() . "\n";
                        }

                        $updatesCount++;
                    }
                }
            }
        }

        if ($updatesCount) {
            $this->output('Join Captcha. Users kicked from telegram groups: ' . $updatesCount);
        }

        return true;
    }

    public function removeGreetingMessages()
    {
        $updatesCount = 0;

        $bots = Bot::findAll([
            'status' => Bot::BOT_STATUS_ENABLED,
        ]);

        if ($bots) {
            foreach ($bots as $bot) {
                $messagesToRemove = BotChatGreeting::find()
                    ->where(['<', 'sent_at', time() - ChatSetting::GREETING_MESSAGE_LIFETIME])
                    ->joinWith('chat')
                    ->andWhere(['bot_chat.bot_id' => $bot->id])
                    ->all();

                if ($messagesToRemove) {
                    $botApi = new \TelegramBot\Api\BotApi($bot->token);

                    foreach ($messagesToRemove as $record) {
                        BotChatGreeting::deleteAll([
                            'chat_id' => $record->chat_id,
                            'provider_user_id' => $record->provider_user_id,
                        ]);

                        try {
                            $botApi->deleteMessage($record->chat->chat_id, $record->message_id);
                        } catch (\Exception $e) {
                            echo 'ERROR: BotChatGreeting #' . $record->id . ' (deleteMessage): ' . $e->getMessage() . "\n";
                        }

                        $updatesCount++;
                    }
                }
            }
        }

        if ($updatesCount) {
            $this->output('Greeting. Greetings removed from telegram groups: ' . $updatesCount);
        }

        return true;
    }
}
