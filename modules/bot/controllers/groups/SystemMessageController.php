<?php

namespace app\modules\bot\controllers\groups;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\BotChatCaptcha;
use app\modules\bot\models\BotChatGreeting;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\User as TelegramUser;
use app\modules\bot\controllers\groups\JoinCaptchaController;

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

            if ($chat->join_hider_status == ChatSetting::STATUS_ON) {
                // Remove join message
                $this->getBotApi()->deleteMessage(
                    $chat->getChatId(),
                    $this->getUpdate()->getMessage()->getMessageId()
                );
            }

            foreach ($this->getUpdate()->getMessage()->getNewChatMembers() as $newChatMember) {
                $telegramUser = TelegramUser::findOne([
                    'provider_user_id' => $newChatMember->getId(),
                ]);

                if (!$telegramUser) {
                    $telegramUser = TelegramUser::createUser($newChatMember);
                    $telegramUser->updateInfo($newChatMember);
                    $telegramUser->save();
                }

                if ($chat->join_captcha_status == ChatSetting::STATUS_ON) {
                    if (!$newChatMember->isBot()) {
                        $role = JoinCaptchaController::ROLE_UNVERIFIED;
                    }
                }

                if (!$chatMember = $chat->getChatMemberByUser($telegramUser)) {
                    $telegramChatMember = $this->getBotApi()->getChatMember(
                        $chat->getChatId(),
                        $telegramUser->provider_user_id
                    );

                    $chat->link('users', $telegramUser, [
                        'status' => $telegramChatMember->getStatus(),
                        'role' => $role,
                    ]);
                } else {
                    $chatMember->setAttributes([
                         'role' => $role,
                    ]);
                    $chatMember->save();
                }

                // Send greeting message
                if ($chat->greeting_status == ChatSetting::STATUS_ON) {
                    if (!$newChatMember->isBot()) {
                        $this->run('greeting/show-greeting', [
                            'telegramUserId' => $telegramUser->id,
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
            $telegramUser = $this->getTelegramUser();

            if ($chat->join_hider_status == ChatSetting::STATUS_ON) {
                // Remove left message
                $this->getBotApi()->deleteMessage(
                    $chat->getChatId(),
                    $this->getUpdate()->getMessage()->getMessageId()
                );
            }

            // Remove captcha message if user left the group
            // Doesn't work if someone kicked the user from the group
            $botCaptcha = BotChatCaptcha::find()
                ->where([
                    'chat_id' => $chat->id,
                    'provider_user_id' => $telegramUser->provider_user_id,
                ])
                ->one();

            if (isset($botCaptcha)) {
                $this->getBotApi()->deleteMessage(
                    $chat->getChatId(),
                    $botCaptcha->captcha_message_id
                );

                $botCaptcha->delete();
            }

            // Remove greeting message if user left the group
            // Doesn't work if someone kicked the user from the group
            $botGreeting = BotChatGreeting::find()
                ->where([
                    'chat_id' => $chat->id,
                    'provider_user_id' => $telegramUser->provider_user_id,
                ])
                ->one();

            if (isset($botGreeting)) {
                $this->getBotApi()->deleteMessage(
                    $chat->getChatId(),
                    $botGreeting->message_id
                );

                $botGreeting->delete();
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
}
