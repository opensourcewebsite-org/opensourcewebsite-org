<?php

namespace app\modules\bot\controllers\publics;

use app\modules\bot\components\Controller;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\models\BotChatCaptcha;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;

/**
* Class SystemMessageController
*
* @package app\controllers\bot
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

            $joinHiderStatus = $chat->getSetting(ChatSetting::JOIN_HIDER_STATUS);

            if (isset($joinHiderStatus) && ($joinHiderStatus->value == ChatSetting::JOIN_HIDER_STATUS_ON)) {
                $deleteMessage = true;
            }

            // Forward to captcha if a new member
            $this->run('join-captcha/show-captcha');

            if ($deleteMessage) {
                return $this->getResponseBuilder()
                    ->deleteMessage()
                    ->build();
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

            $deleteMessage = false;
            $joinHiderStatus = $chat->getSetting(ChatSetting::JOIN_HIDER_STATUS);

            if (isset($joinHiderStatus) && $joinHiderStatus->value == ChatSetting::JOIN_HIDER_STATUS_ON) {
                $deleteMessage = true;
            }

            // Remove captcha info if user left channel
            $botCaptcha = BotChatCaptcha::find()
                ->where([
                    'chat_id' => $chat->id,
                    'provider_user_id' => $telegramUser->provider_user_id,
                ])
                ->one();

            if (isset($botCaptcha)) {
                $this->getBotApi()->deleteMessage(
                    $chat->chat_id,
                    $botCaptcha->captcha_message_id
                );
            }

            BotChatCaptcha::removeCaptchaInfo($chat->id, $telegramUser->provider_user_id);

            if ($deleteMessage) {
                return $this->getResponseBuilder()
                    ->deleteMessage()
                    ->build();
            }
        }
    }

    public function actionGroupToSupergroup()
    {
        if ($update->getMessage()->getMigrateToChatId()) {
            $chat = $this->getTelegramChat();

            $chat->setAttributes([
                'type' => Chat::TYPE_SUPERGROUP,
                'chat_id' => $this->getMessage()->getMigrateToChatId(),
            ]);

            $chat->save();
        }
    }
}
