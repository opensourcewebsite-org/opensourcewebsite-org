<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use app\modules\bot\controllers\groups\JoinCaptchaController;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatCaptcha;
use app\modules\bot\models\ChatGreeting;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
use Yii;

/**
* Class SystemMessageController
*
* @package app\modules\bot\controllers\groups
*/
class SystemMessageController extends Controller
{
    /**
    * @return array
    */
    public function actionNewChatMembers()
    {
        if ($this->getUpdate()->getMessage()->getNewChatMembers()) {
            $chat = $this->getTelegramChat();

            $role = JoinCaptchaController::ROLE_VERIFIED;

            if ($chat->isJoinHiderOn()) {
                if ($chat->filter_remove_member_joined == ChatSetting::STATUS_ON) {
                    $this->getBotApi()->deleteMessage(
                        $chat->getChatId(),
                        $this->getUpdate()->getMessage()->getMessageId()
                    );
                }
            }

            foreach ($this->getUpdate()->getMessage()->getNewChatMembers() as $newChatMember) {
                $user = User::findOne([
                    'provider_user_id' => $newChatMember->getId(),
                ]);

                if (!$user) {
                    $user = User::createUser($newChatMember);
                    $user->updateInfo($newChatMember);
                    $user->save();
                }

                if ($chat->isJoinCaptchaOn()) {
                    if (!$newChatMember->isBot()) {
                        $role = JoinCaptchaController::ROLE_UNVERIFIED;
                    }
                }

                if (!$chatMember = $chat->getChatMemberByUser($user)) {
                    $botApiChatMember = $this->getBotApi()->getChatMember(
                        $chat->getChatId(),
                        $user->provider_user_id
                    );

                    // TODO Error: Call to a member function getStatus() on bool in
                    if ($botApiChatMember) {
                        $fromUserId = null;

                        if (!empty($this->getUpdate()->getFrom()) && !empty($this->getUpdate()->getFrom()->getId())) {
                            $fromUser = User::findOne([
                                'provider_user_id' => $this->getUpdate()->getFrom()->getId(),
                            ]);

                            if ($fromUser) {
                                $fromUserId = $fromUser->id;
                            }
                        }

                        $chat->link('users', $user, [
                            'status' => $botApiChatMember->getStatus(),
                            'role' => $role,
                            'invite_user_id' => $fromUserId,
                        ]);
                    }
                } else {
                    $chatMember->setAttributes([
                         'role' => $role,
                    ]);

                    $chatMember->save();
                }

                // Send greeting message
                if ($chat->isGreetingOn()) {
                    if (!$newChatMember->isBot()) {
                        $this->run('greeting/show-greeting', [
                            'telegramUserId' => $user->id,
                        ]);
                    }
                }
            }
        }
    }

    /**
    * @return array
    */
    public function actionLeftChatMember()
    {
        if ($this->getUpdate()->getMessage()->getLeftChatMember()) {
            $chat = $this->getTelegramChat();
            $user = $this->getTelegramUser();

            if ($chat->isJoinHiderOn()) {
                if ($chat->filter_remove_member_left == ChatSetting::STATUS_ON) {
                    $this->getBotApi()->deleteMessage(
                        $chat->getChatId(),
                        $this->getUpdate()->getMessage()->getMessageId()
                    );
                }
            }

            // Remove captcha message if user left the group
            // Doesn't work if someone kicked the user from the group
            $chatCaptcha = ChatCaptcha::find()
                ->where([
                    'chat_id' => $chat->id,
                    'provider_user_id' => $user->provider_user_id,
                ])
                ->one();

            if (isset($chatCaptcha)) {
                $this->getBotApi()->deleteMessage(
                    $chat->getChatId(),
                    $chatCaptcha->captcha_message_id
                );

                $chatCaptcha->delete();
            }

            // Remove greeting message if user left the group
            // Doesn't work if someone kicked the user from the group
            $chatGreeting = ChatGreeting::find()
                ->where([
                    'chat_id' => $chat->id,
                    'provider_user_id' => $user->provider_user_id,
                ])
                ->one();

            if (isset($chatGreeting)) {
                $this->getBotApi()->deleteMessage(
                    $chat->getChatId(),
                    $chatGreeting->message_id
                );

                $chatGreeting->delete();
            }
        }
    }

    public function actionGroupToSupergroup()
    {
        if ($this->getUpdate()->getMessage()->getMigrateToChatId()) {
            $chat = $this->getTelegramChat();

            $chat->setAttributes([
                'type' => Chat::TYPE_SUPERGROUP,
                'chat_id' => $this->getMessage()->getMigrateToChatId(),
            ]);

            $chat->save();
        }
    }

    /**
    * @return array
    */
    public function actionVideoChatScheduled()
    {
        if ($this->getUpdate()->getMessage()->getVideoChatScheduled()) {
            $chat = $this->getTelegramChat();

            if ($chat->isJoinHiderOn()) {
                if ($chat->filter_remove_video_chat_scheduled == ChatSetting::STATUS_ON) {
                    $this->getBotApi()->deleteMessage(
                        $chat->getChatId(),
                        $this->getUpdate()->getMessage()->getMessageId()
                    );
                }
            }
        }
    }

    /**
    * @return array
    */
    public function actionVideoChatStarted()
    {
        if ($this->getUpdate()->getMessage()->getVideoChatStarted()) {
            $chat = $this->getTelegramChat();

            if ($chat->isJoinHiderOn()) {
                if ($chat->filter_remove_video_chat_started == ChatSetting::STATUS_ON) {
                    $this->getBotApi()->deleteMessage(
                        $chat->getChatId(),
                        $this->getUpdate()->getMessage()->getMessageId()
                    );
                }
            }
        }
    }

    /**
    * @return array
    */
    public function actionVideoChatEnded()
    {
        if ($this->getUpdate()->getMessage()->getVideoChatEnded()) {
            $chat = $this->getTelegramChat();

            if ($chat->isJoinHiderOn()) {
                if ($chat->filter_remove_video_chat_ended == ChatSetting::STATUS_ON) {
                    $this->getBotApi()->deleteMessage(
                        $chat->getChatId(),
                        $this->getUpdate()->getMessage()->getMessageId()
                    );
                }
            }
        }
    }

    /**
    * @return array
    */
    public function actionVideoChatParticipantsInvited()
    {
        if ($this->getUpdate()->getMessage()->getVideoChatParticipantsInvited()) {
            $chat = $this->getTelegramChat();

            if ($chat->isJoinHiderOn()) {
                if ($chat->filter_remove_video_chat_invited == ChatSetting::STATUS_ON) {
                    $this->getBotApi()->deleteMessage(
                        $chat->getChatId(),
                        $this->getUpdate()->getMessage()->getMessageId()
                    );
                }
            }
        }
    }
}
