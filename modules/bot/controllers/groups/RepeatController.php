<?php

namespace app\modules\bot\controllers\groups;

use app\components\helpers\TimeHelper;
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
        // delete /repeat message
        if ($this->getUpdate() && $this->getUpdate()->getMessage() && !$this->getUpdate()->getCallbackQuery()) {
            $this->getResponseBuilder()
                ->deleteMessage()
                ->send();
        }

        if (!isset($time)) {
            $offset = $this->getGlobalUser()->timezone;
            $dateTimeZone = new DateTimeZone(TimeHelper::getTimezoneByOffset($offset));
            $time = new DateTime('now', $dateTimeZone);
            $time = $time->format('H:i');
        }

        if (($postTime = TimeHelper::getMinutesByTimeOfDay($time)) === null) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if (!isset($skipDays) || !is_numeric($skipDays)) {
            $skipDays = 0;
        }

        $user = $this->getTelegramUser();
        $chat = $this->getTelegramChat();
        $chatMember = $chat->getChatMemberByUser($user);

        if ($chat->isGroup() && $chatMember->isActiveAdministrator() && $replyMessage = $this->getMessage()->getReplyToMessage()) {
            $replyUser = User::findOne([
                'provider_user_id' => $replyMessage->getFrom()->getId(),
            ]);

            if (!isset($replyUser)) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery()
                    ->build();
            }

            $replyChatMember = ChatMember::findOne([
                'chat_id' => $chat->id,
                'user_id' => $replyUser->id,
            ]);

            if (!isset($replyChatMember)) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery()
                    ->build();
            }

            // check is post exist
            $post = ChatPublisherPost::findOne([
                'chat_id' => $chat->id,
                'text'=>$replyMessage->getText(),
                'status' => 1,
                'time' => $postTime,
                'skip_days' => $skipDays,
            ]);

            if (isset($post)) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery()
                    ->build();
            }

            // create new ChatPublisherPost
            $post = new ChatPublisherPost([
                'chat_id' => $chat->id,
                'text'=>$replyMessage->getText(),
                'status' => 1,
                'time' => $postTime,
                'skip_days' => $skipDays,
            ]);

            $post->save();

            $user->sendMessage(
                $this->render('/privates/repeat', [
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
                ]
            );
        }

        return [];
    }
}