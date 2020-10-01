<?php

namespace app\modules\bot\controllers\groups;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\BotChatCaptcha;

/**
 * Class MessageController
 *
 * @package app\modules\bot\controllers\groups
 */
class MessageController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $telegramUser = $this->getTelegramUser();
        $telegramChat = $this->getTelegramChat();

        $joinCaptchaStatus = $telegramChat->getSetting(ChatSetting::JOIN_CAPTCHA_STATUS);

        if (isset($joinCaptchaStatus) && ($joinCaptchaStatus->value == ChatSetting::JOIN_CAPTCHA_STATUS_ON)) {
            $chatMember = ChatMember::findOne([
                'chat_id' => $telegramChat->id,
                'user_id' => $telegramUser->id,
            ]);

            if (($chatMember->role == JoinCaptchaController::ROLE_UNVERIFIED) && !$chatMember->isAdmin()) {
                $this->getBotApi()->deleteMessage(
                    $telegramChat->chat_id,
                    $this->getUpdate()->getMessage()->getMessageId()
                );

                $botCaptcha = BotChatCaptcha::find()
                    ->where([
                        'chat_id' => $telegramChat->id,
                        'provider_user_id' => $telegramUser->provider_user_id,
                    ])
                    ->one();

                // Forward to captcha if a new member
                if (!isset($botCaptcha)) {
                    $this->run('join-captcha/show-captcha');
                }
            }
        }

        $messageFilterStatus = $telegramChat->getSetting(ChatSetting::FILTER_STATUS);
        $messageFilterMode = $telegramChat->getSetting(ChatSetting::FILTER_MODE);

        $deleteMessage = false;

        if (isset($messageFilterStatus) && isset($messageFilterMode) && ($messageFilterStatus->value == ChatSetting::FILTER_STATUS_ON)) {
            if ($this->getMessage()->getText() !== null) {
                $adminUser = $telegramChat->getAdministrators()
                    ->where([
                        'id' => $telegramUser->user_id,
                    ])
                    ->one();

                if (!isset($adminUser)) {
                    if ($messageFilterMode->value == ChatSetting::FILTER_MODE_BLACKLIST) {
                        $deleteMessage = false;

                        $phrases = $telegramChat->getBlacklistPhrases()->all();

                        foreach ($phrases as $phrase) {
                            if (mb_stripos($this->getMessage()->getText(), $phrase->text) !== false) {
                                $deleteMessage = true;
                                break;
                            }
                        }
                    } else {
                        $deleteMessage = true;

                        $phrases = $telegramChat->getWhitelistPhrases()->all();

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
                $this->getBotApi()->deleteMessage(
                    $telegramChat->chat_id,
                    $this->getUpdate()->getMessage()->getMessageId()
                );
            }
        }

        return [];
    }
}
