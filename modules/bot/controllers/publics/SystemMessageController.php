<?php

namespace app\modules\bot\controllers\publics;

use app\modules\bot\components\Controller;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\BotChatCaptcha;

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
    public function actionIndex()
    {
        $chat = $this->getTelegramChat();

        $deleteMessage = false;
        $joinHiderStatus = $chat->getSetting(ChatSetting::JOIN_HIDER_STATUS);

        if (isset($joinHiderStatus) && $joinHiderStatus->value == ChatSetting::JOIN_HIDER_STATUS_ON) {
            $deleteMessage = true;
        }

        //Remove captcha info if user left channel
        if ($this->getUpdate()->getMessage()->getLeftChatMember()) {
            $telegramUser = $this->getTelegramUser();
            $chat = $this->getTelegramChat();

            $botCaptcha = BotChatCaptcha::find()->where([
                'chat_id' => $chat->id,
                'provider_user_id' => $telegramUser->provider_user_id
            ])->one();

            if (isset($botCaptcha)) {
                $captchaMessageId = $botCaptcha->captcha_message_id;

                $this->getBotApi()->deleteMessage(
                    $chat->chat_id,
                    $captchaMessageId
                );

            }

            BotChatCaptcha::removeCaptchaInfo($chat->id, $telegramUser->provider_user_id);

        }

        // forward to captcha if a new member
        if ($this->getUpdate()->getMessage()->getNewChatMember()) {
            $this->run('join-captcha/show-captcha');
        }

        if ($deleteMessage) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->deleteMessage()
                ->build();
        }

    }

    public function actionGroupToSupergroup()
    {
        $chat = $this->getTelegramChat();

        $chat->setAttributes([
            'type' => Chat::TYPE_SUPERGROUP,
            'chat_id' => $this->getMessage()->getMigrateToChatId(),
        ]);

        $chat->save();

        return [];
    }
}
