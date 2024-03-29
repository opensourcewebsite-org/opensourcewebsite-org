<?php

namespace app\modules\bot\controllers\groups;

use app\components\helpers\TimeHelper;
use Codeception\PHPUnit\ResultPrinter\UI;
use DateTime;
use DateTimeZone;
use app\modules\bot\components\Controller;
use app\modules\bot\controllers\privates\GroupPublisherController;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatPublisherPost;
use app\modules\bot\models\User;
use Yii;

/**
 * Class RepeatController
 *
 * @package app\modules\bot\controllers\groups
 */
class RepeatController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($time = null, $skipDays = null)
    {
        if ($this->getUpdate() && $this->getUpdate()->getMessage() && !$this->getUpdate()->getCallbackQuery()) {
            $this->getResponseBuilder()
                ->deleteMessage()
                ->send();
        }

        if (!isset($time)) {
            $offset = $this->getTelegramChat()->timezone;
            $dateTimeZone = new DateTimeZone(TimeHelper::getTimezoneByOffset($offset));
            $time = new DateTime('now', $dateTimeZone);
            $time = $time->format('H:i');
        }

        if (($postTime = TimeHelper::getMinutesByTimeOfDay($time)) === null) {
            return [];
        }

        if (!isset($skipDays) || !is_numeric($skipDays)) {
            $skipDays = 0;
        }

        $user = $this->getTelegramUser();
        $chat = $this->getTelegramChat();
        $chatMember = $chat->getChatMemberByUser($user);

        if ($chat->isPublisherOn() && ($chatMember->isActiveAdministrator() || $chatMember->isAnonymousAdministrator()) && $replyMessage = $this->getMessage()->getReplyToMessage()) {
            $replyUser = User::findOne([
                'provider_user_id' => $replyMessage->getFrom()->getId(),
            ]);

            if (!isset($replyUser)) {
                return [];
            }

            $replyChatMember = ChatMember::findOne([
                'chat_id' => $chat->id,
                'user_id' => $replyUser->id,
            ]);

            if (!isset($replyChatMember)) {
                return [];
            }

            $post = ChatPublisherPost::findOne([
                'chat_id' => $chat->id,
                'text' => $replyMessage->getText(),
                'time' => $postTime,
                'topic_id' => $this->getMessage()->isTopicMessage() ? $this->getMessage()->getMessageThreadId() : null,
            ]);

            if (!isset($post)) {
                $post = new ChatPublisherPost([
                    'chat_id' => $chat->id,
                    'text' => $replyMessage->getText(),
                    'time' => $postTime,
                    'topic_id' => $this->getMessage()->isTopicMessage() ? $this->getMessage()->getMessageThreadId() : null,
                ]);
            }

            $post->status = ChatPublisherPost::STATUS_ON;
            $post->skip_days = $skipDays;
            $post->save();

            $user->sendMessage(
                $this->render('/privates/post', [
                    'post' => $post,
                ]),
                [
                    [
                        [
                            'callback_data' => GroupPublisherController::createRoute('post', [
                                'id' => $post->id,
                            ]),
                            'text' => Yii::t('bot', 'Post'),
                        ],
                    ],
                ],
            );
        }

        return [];
    }

    /**
     * @return array
     */
    public function actionOff()
    {
        if ($this->getUpdate() && $this->getUpdate()->getMessage() && !$this->getUpdate()->getCallbackQuery()) {
            $this->getResponseBuilder()
                ->deleteMessage()
                ->send();
        }

        $user = $this->getTelegramUser();
        $chat = $this->getTelegramChat();
        $chatMember = $chat->getChatMemberByUser($user);

        if ($chat->isGroup() && ($chatMember->isActiveAdministrator() || $chatMember->isAnonymousAdministrator()) && $replyMessage = $this->getMessage()->getReplyToMessage()) {
            $replyUser = User::findOne([
                'provider_user_id' => $replyMessage->getFrom()->getId(),
            ]);

            if (!isset($replyUser) || !$replyUser->isBot()) {
                return [];
            }

            $replyChatMember = ChatMember::findOne([
                'chat_id' => $chat->id,
                'user_id' => $replyUser->id,
            ]);

            if (!isset($replyChatMember)) {
                return [];
            }

            $post = ChatPublisherPost::findOne([
                'chat_id' => $chat->id,
                'text' => $replyMessage->getText(),
                'topic_id' => $this->getMessage()->isTopicMessage() ? $this->getMessage()->getMessageThreadId() : null,
            ]);

            if (!isset($post)) {
                return [];
            }

            $post->status = ChatPublisherPost::STATUS_OFF;
            $post->save();

            $user->sendMessage(
                $this->render('/privates/post', [
                    'post' => $post,
                ]),
                [
                    [
                        [
                            'callback_data' => GroupPublisherController::createRoute('post', [
                                'id' => $post->id,
                            ]),
                            'text' => Yii::t('bot', 'Post'),
                        ],
                    ],
                ],
            );
        }

        return [];
    }
}
