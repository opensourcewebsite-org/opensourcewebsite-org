<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\modules\bot\models\Bot;
use app\modules\bot\models\ChatCaptcha;
use app\modules\bot\models\ChatGreeting;
use app\modules\bot\models\ChatPublisherPost;
use app\modules\bot\models\ChatSetting;
use Yii;
use yii\console\Controller;

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
        $this->actionUpdatePublisherPostsNextSendAt();
        $this->actionSendPublisherMessages();
    }

    /**
     * Enable webhook for telegram bot
     *
     * @throws \TelegramBot\Api\Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionEnableWebhook()
    {
        $bot = new Bot();

        if ($bot->setWebhook()) {
            echo 'The bot "' . $bot->username . '" has been enabled' . "\n";
        }
    }

    /**
     * Disable webhook for telegram bot
     *
     * @throws \TelegramBot\Api\Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDisableWebhook()
    {
        $bot = new Bot();

        if ($bot->deleteWebhook()) {
            echo 'The bot "' . $bot->username . '" has been disabled' . "\n";
        }
    }

    public function actionRemoveCaptchaMessages()
    {
        $updatesCount = 0;

        $messagesToRemove = ChatCaptcha::find()
            ->where([
                '<', 'sent_at', time() - ChatSetting::JOIN_CAPTCHA_MESSAGE_LIFETIME,
            ])
            ->all();

        if ($messagesToRemove) {
            $bot = new Bot();
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
                    $botApi->banChatMember($record->chat->chat_id, $record->provider_user_id);
                } catch (\Exception $e) {
                    echo 'ERROR: ChatCaptcha #' . $record->id . ' (banChatMember): ' . $e->getMessage() . "\n";
                }

                $updatesCount++;
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

        $messagesToRemove = ChatGreeting::find()
            ->where([
                '<', 'sent_at', time() - ChatSetting::GREETING_MESSAGE_LIFETIME,
            ])
            ->all();

        if ($messagesToRemove) {
            $bot = new Bot();
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

        if ($updatesCount) {
            $this->output('Greeting. Greetings removed from telegram groups: ' . $updatesCount);
        }

        return true;
    }

    public function actionUpdatePublisherPostsNextSendAt()
    {
        $updatedCount = 0;

        $models = ChatPublisherPost::find()
            ->andWhere([
                ChatPublisherPost::tableName() . '.status' => ChatPublisherPost::STATUS_ON,
                ChatPublisherPost::tableName() . '.next_sent_at' => null,
            ])
            ->joinWith('chat.settings')
            ->andWhere([
                'and',
                [ChatSetting::tableName() . '.setting' => 'publisher_status'],
                [ChatSetting::tableName() . '.value' => ChatSetting::STATUS_ON],
            ])
            ->all();

        if ($models) {
            foreach ($models as $post) {
                $this->debug('Post ID: ' . $post->id);

                $post->setNextSendAt();
                $post->save(false);

                $this->debug('Next Send At: ' . $post->getNextSendAt());

                $updatedCount++;
            }

        }

        if ($updatedCount) {
            $this->output('Publisher. Posts (nextSendAt) updated: ' . $updatedCount);
        }

        return true;
    }

    public function actionSendPublisherMessages()
    {
        $sentCount = 0;
        $skipChatIds = [];

        do {
            $post = ChatPublisherPost::find()
                ->andWhere([
                    ChatPublisherPost::tableName() . '.status' => ChatPublisherPost::STATUS_ON,
                ])
                ->andWhere([
                    '<', ChatPublisherPost::tableName() . '.next_sent_at', time(),
                ])
                ->andWhere([
                    'not', [ChatPublisherPost::tableName() . '.next_sent_at' => null],
                ])
                ->andWhere([
                    'not', [ChatPublisherPost::tableName() . '.chat_id' => $skipChatIds],
                ])
                ->joinWith('chat.settings')
                ->andWhere([
                    'and',
                    [ChatSetting::tableName() . '.setting' => 'publisher_status'],
                    [ChatSetting::tableName() . '.value' => ChatSetting::STATUS_ON],
                ])
                ->one();

            if ($post) {
                $this->debug('Post ID: ' . $post->id);
                $chat = $post->chat;

                if (!isset($module)) {
                    $module = Yii::$app->getModule('bot');
                    $bot = new Bot();
                    $module->setBot($bot);
                }

                $module->setChat($chat);
                $response = $module->runAction('publisher/send-message', [
                    'id' => $post->id,
                ]);

                if ($response) {
                    $skipChatIds[] = $chat->id;
                    $sentCount++;
                    sleep(1);
                }

                $post->refresh();

                $this->debug('Next Send At: ' . $post->getNextSendAt());
            }
        } while ($post);

        if ($sentCount) {
            $this->output('Publisher. Posts sent to telegram groups: ' . $sentCount);
        }

        return true;
    }
}
