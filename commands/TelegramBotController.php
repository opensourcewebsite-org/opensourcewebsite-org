<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\modules\bot\models\Bot;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatCaptcha;
use app\modules\bot\models\ChatGreeting;
use app\modules\bot\models\ChatMarketplacePost;
use app\modules\bot\models\ChatSetting;
use Yii;
use yii\console\Controller;
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
        $this->actionRemoveCaptchaMessages();
        //$this->actionRemoveGreetingMessages();
        //$this->actionUpdateMarketplacePostsNextSendAt();
        //$this->actionSendMarketplaceMessages();
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
        $bots = Bot::findAll([
                'status' => Bot::BOT_STATUS_DISABLED,
                ]);

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
        $bots = Bot::findAll([
                'status' => Bot::BOT_STATUS_ENABLED,
                ]);

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
            $botApi = $bot->botApi;

            $botApiUser = $botApi->getMe();

            $bot->name = $botApiUser->getUsername();
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

    public function actionRemoveCaptchaMessages()
    {
        $updatesCount = 0;

        $bots = Bot::findAll([
            'status' => Bot::BOT_STATUS_ENABLED,
        ]);

        if ($bots) {
            foreach ($bots as $bot) {
                $messagesToRemove = ChatCaptcha::find()
                    ->where([
                        '<', 'sent_at', time() - ChatSetting::JOIN_CAPTCHA_MESSAGE_LIFETIME,
                    ])
                    ->joinWith('chat')
                    ->andWhere([
                        Chat::tableName() . '.bot_id' => $bot->id,
                    ])
                    ->all();

                if ($messagesToRemove) {
                    $botApi = $bot->botApi;

                    foreach ($messagesToRemove as $record) {
                        ChatCaptcha::deleteAll([
                            'chat_id' => $record->chat_id,
                            'provider_user_id' => $record->provider_user_id,
                        ]);

                        try {
                            $botApi->deleteMessage($record->chat->chat_id, $record->captcha_message_id);
                        } catch (\Exception $e) {
                            echo 'ERROR: ChatCaptcha #' . $record->id . ' (deleteMessage): ' . $e->getMessage() . "\n";
                        }

                        try {
                            $botApi->kickChatMember($record->chat->chat_id, $record->provider_user_id);
                        } catch (\Exception $e) {
                            echo 'ERROR: ChatCaptcha #' . $record->id . ' (kickChatMember): ' . $e->getMessage() . "\n";
                        }

                        $updatesCount++;
                    }
                }
            }
        }

        if ($updatesCount) {
            $this->output('Captcha. Users kicked from telegram groups: ' . $updatesCount);
        }

        return true;
    }

    public function actionRemoveGreetingMessages()
    {
        $updatesCount = 0;

        $bots = Bot::findAll([
            'status' => Bot::BOT_STATUS_ENABLED,
        ]);

        if ($bots) {
            foreach ($bots as $bot) {
                $messagesToRemove = ChatGreeting::find()
                    ->where([
                        '<', 'sent_at', time() - ChatSetting::GREETING_MESSAGE_LIFETIME,
                    ])
                    ->joinWith('chat')
                    ->andWhere([
                        Chat::tableName() . '.bot_id' => $bot->id,
                    ])
                    ->all();

                if ($messagesToRemove) {
                    $botApi = $bot->botApi;

                    foreach ($messagesToRemove as $record) {
                        ChatGreeting::deleteAll([
                            'chat_id' => $record->chat_id,
                            'provider_user_id' => $record->provider_user_id,
                        ]);

                        try {
                            $botApi->deleteMessage($record->chat->chat_id, $record->message_id);
                        } catch (\Exception $e) {
                            echo 'ERROR: ChatGreeting #' . $record->id . ' (deleteMessage): ' . $e->getMessage() . "\n";
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

    public function actionUpdateMarketplacePostsNextSendAt()
    {
        $updatedCount = 0;

        $bots = Bot::findAll([
            'status' => Bot::BOT_STATUS_ENABLED,
        ]);

        if ($bots) {
            foreach ($bots as $bot) {
                $models = ChatMarketplacePost::find()
                    ->andWhere([
                        ChatMarketplacePost::tableName() . '.status' => ChatMarketplacePost::STATUS_ON,
                        ChatMarketplacePost::tableName() . '.next_send_at' => null,
                    ])
                    ->joinWith('chat')
                    ->andWhere([
                        Chat::tableName() . '.bot_id' => $bot->id,
                    ])
                    ->joinWith('chat.settings')
                    ->andWhere([
                        'and',
                        [ChatSetting::tableName() . '.setting' => 'marketplace_status'],
                        [ChatSetting::tableName() . '.value' => ChatSetting::STATUS_ON],
                    ])
                    ->all();

                if ($models) {
                    $this->debug('Update nextSendAt');

                    foreach ($models as $post) {
                        $this->debug('Post ID: ' . $post->id);
                        $chatMember = $post->chatMember;
                        $chat = $chatMember->chat;

                        if (!$chatMember->canUseMarketplace()
                            || ($chat->isLimiterOn() && !$chatMember->isCreator() && !$chatMember->hasLimiter())) {
                            $post->setInactive()->save();
                        } else {
                            $post->updateNextSendAt();
                            $this->debug('Next Send At: ' . $post->getNextSendAt());

                            $updatedCount++;
                        }
                    }
                }
            }
        }

        if ($updatedCount) {
            $this->output('Marketplace. Posts (nextSendAt) updated: ' . $updatedCount);
        }

        return true;
    }

    // TODO
    // https://core.telegram.org/bots/faq#broadcasting-to-users
    public function actionSendMarketplaceMessages()
    {
        $sentCount = 0;

        $bots = Bot::findAll([
            'status' => Bot::BOT_STATUS_ENABLED,
        ]);

        if ($bots) {
            foreach ($bots as $bot) {
                $botApi = $bot->botApi;

                $this->debug('Sending');

                $skipChatIds = [];
                $module = null;

                do {
                    $post = ChatMarketplacePost::find()
                        ->andWhere([
                            ChatMarketplacePost::tableName() . '.status' => ChatMarketplacePost::STATUS_ON,
                        ])
                        ->andWhere([
                            '<', ChatMarketplacePost::tableName() . '.next_send_at', time(),
                        ])
                        ->andWhere([
                            'not', [ChatMarketplacePost::tableName() . '.next_send_at' => null],
                        ])
                        ->joinWith('chatMember.chat')
                        ->andWhere([
                            Chat::tableName() . '.bot_id' => $bot->id,
                        ])
                        ->andWhere([
                            'not', [Chat::tableName() . '.id' => $skipChatIds],
                        ])
                        ->joinWith('chatMember.chat.settings')
                        ->andWhere([
                            'and',
                            [ChatSetting::tableName() . '.setting' => 'marketplace_status'],
                            [ChatSetting::tableName() . '.value' => ChatSetting::STATUS_ON],
                        ])
                        ->orderByRank()
                        ->one();

                    if ($post) {
                        $this->debug('Post ID: ' . $post->id);
                        $chatMember = $post->chatMember;
                        $chat = $chatMember->chat;

                        if (!$chatMember->canUseMarketplace()
                            || ($chat->isLimiterOn() && !$chatMember->isCreator() && !$chatMember->hasLimiter())) {
                            $post->setInactive()->save();
                        } else {
                            if ($chat->isSlowModeOn() && !$chatMember->isCreator()) {
                                if (!$chatMember->checkSlowMode()) {
                                    $post->updateNextSendAt();
                                    $this->debug('Next Send At: ' . $post->getNextSendAt());

                                    continue;
                                } else {
                                    $isSlowModeOn = true;
                                }
                            }

                            if (!isset($module)) {
                                $module = Yii::$app->getModule('bot');
                                $module->setBot($bot);
                            }

                            $module->setChat($chat);
                            $module->runAction('marketplace/send-message');

                            $response = false;

                            if ($response) {
                                if (isset($isSlowModeOn) && $isSlowModeOn) {
                                    $chatMember->updateSlowMode($response->getDate());
                                }

                                $post->sent_at = $response->getDate();
                                $post->provider_message_id = $response->getMessageId();
                                $post->save(false);

                                // ChatCaptcha::deleteAll([
                            //     'chat_id' => $record->chat_id,
                            //     'provider_user_id' => $record->provider_user_id,
                                // ]);
                            //
                                // try {
                            //     $botApi->deleteMessage($record->chat->chat_id, $record->captcha_message_id);
                                // } catch (\Exception $e) {
                            //     echo 'ERROR: ChatMarketplacePost #' . $record->id . ' (deleteMessage): ' . $e->getMessage() . "\n";
                                // }
                            //
                                // try {
                            //     $botApi->kickChatMember($record->chat->chat_id, $record->provider_user_id);
                                // } catch (\Exception $e) {
                            //     echo 'ERROR: ChatMarketplacePost #' . $record->id . ' (kickChatMember): ' . $e->getMessage() . "\n";
                                // }

                                $skipChatIds[] = $chat->id;
                                $sentCount++;
                                sleep(1);
                            }

                            $post->updateNextSendAt();
                            $this->debug('Next Send At: ' . $post->getNextSendAt());
                        }
                    }
                } while ($post);
            }
        }

        if ($sentCount) {
            $this->output('Marketplace. Posts sent to telegram groups: ' . $sentCount);
        }

        return true;
    }
}
