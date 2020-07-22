<?php

namespace app\modules\bot\controllers\publics;

use app\modules\bot\components\Controller;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;

/**
 * Class MessageController
 *
 * @package app\controllers\bot
 */
class MessageController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $telegramUser = $this->getTelegramUser();
        $chat = $this->getTelegramChat();

        $statusSetting = $chat->getSetting(ChatSetting::FILTER_STATUS);
        $modeSetting = $chat->getSetting(ChatSetting::FILTER_MODE);

        $captchaSetting = $chat->getSetting(ChatSetting::JOIN_CAPTCHA_STATUS);

        if ((!isset($statusSetting) || !isset($modeSetting) || $statusSetting->value == ChatSetting::FILTER_STATUS_OFF) && $captchaSetting->value == ChatSetting::JOIN_CAPTCHA_STATUS_OFF) {
            return [];
        }

        /* Captcha handle*/
        if($captchaSetting->value == ChatSetting::JOIN_CAPTCHA_STATUS_ON){
            $chatMember = ChatMember::findOne(['chat_id' => $chat->id, 'user_id' => $telegramUser->id ]);
            if($chatMember->role == JoinCaptchaController::ROLE_UNVERIFIED) {
                return $this->getResponseBuilder()
                    ->deleteMessage()
                    ->build();
            }
        }

        $deleteMessage = false;

        if ($this->getMessage()->getText() !== null) {

            $adminUser = $chat->getAdministrators()->where(['id' => $telegramUser->user_id])->one();

            if (!isset($adminUser)) {

                if ($modeSetting->value == ChatSetting::FILTER_MODE_BLACKLIST) {
                    $deleteMessage = false;

                    $phrases = $chat->getBlacklistPhrases()->all();

                    foreach ($phrases as $phrase) {
                        if (mb_stripos($this->getMessage()->getText(), $phrase->text) !== false) {
                            $deleteMessage = true;
                            break;
                        }
                    }
                } else {
                    $deleteMessage = true;

                    $phrases = $chat->getWhitelistPhrases()->all();

                    foreach ($phrases as $phrase) {
                        if (mb_stripos($this->getMessage()->getText(), $phrase->text) !== false) {
                            $deleteMessage = false;
                            break;
                        }
                    }
                }

            }
        }

        if ($deleteMessage) {
            return $this->getResponseBuilder()
                ->deleteMessage()
                ->build();
        }
    }
}
