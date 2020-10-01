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
use app\models\UaLawmakingVoting;

/**
 * Class BotCommandController
 *
 * @package app\commands
 */
class BotCommandController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    public function actionIndex()
    {
        $this->removeUnverifiedUsers();
        $this->removeGreetings();
        $this->sendMessagesToUaLawmakingChannel();
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

        $bots = Bot::findAll([
            'status' => Bot::BOT_STATUS_ENABLED,
        ]);

        if ($bots) {
            foreach ($bots as $bot) {
                $messagesToRemove = BotChatCaptcha::find()
                    ->where(['<', 'sent_at', time() - ChatSetting::JOIN_CAPTCHA_LIFETIME_DEFAULT])
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

    public function removeGreetings()
    {
        $updatesCount = 0;

        $bots = Bot::findAll([
            'status' => Bot::BOT_STATUS_ENABLED,
        ]);

        if ($bots) {
            foreach ($bots as $bot) {
                $messagesToRemove = BotChatGreeting::find()
                    ->where(['<', 'sent_at', time() - ChatSetting::GREETING_LIFETIME_DEFAULT])
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

    public function sendMessagesToUaLawmakingChannel()
    {
        $updatesCount = 0;

        if (isset(Yii::$app->params['bot']['ua_lawmaking'])) {
            $votings = UaLawmakingVoting::find()
                ->where([
                    'sent_at' => null,
                ])
                ->count();

            if (!$votings) {
                return false;
            }

            $bot = Bot::findOne([
                'status' => Bot::BOT_STATUS_ENABLED,
            ]);

            if ($bot) {
                $module = Yii::$app->getModule('bot');
                $module->setBot($bot);
                $module->setChatByChatId(Yii::$app->params['bot']['ua_lawmaking']['chat_id']);
                if ($module->initFromConsole()) {
                    $module->runAction('ua-lawmaking/show-new-voting');

                    return true;
                }
            }
        } else {
            $this->console('Missing params in config/params.php');
        }

        return false;
    }
}
