<?php

namespace app\modules\bot\controllers\publics;

use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\components\Controller;
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

            $telegramUser = $this->getTelegramUser();
            BotChatCaptcha::removeCaptchaInfo($chat->id,$telegramUser->provider_user_id);

        }

        if ($deleteMessage) {
            return $this->getResponseBuilder()
            ->deleteMessage()
            ->build();
        }
    }

    public function actionShowCaptcha()
    {
        $chat = $this->getTelegramChat();
        $joinCaptchaStatus = $chat->getSetting(ChatSetting::JOIN_CAPTCHA_STATUS);

        if (isset($joinCaptchaStatus) && $joinCaptchaStatus->value == ChatSetting::JOIN_CAPTCHA_STATUS_ON) {

            $telegramUser = $this->getTelegramUser();

            $needShowCaptcha = BotChatCaptcha::checkCaptcha($chat->id,$telegramUser->provider_user_id);

            if ($needShowCaptcha) {

                $mesageText = new MessageText('Here will be a captcha action');
                return $this->getResponseBuilder()
                    ->sendMessage($mesageText)
                    ->build();
            }
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
