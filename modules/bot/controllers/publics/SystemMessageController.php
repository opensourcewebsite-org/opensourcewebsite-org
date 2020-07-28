<?php

namespace app\modules\bot\controllers\publics;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\BotChatCaptcha;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use TelegramBot\Api\HttpException;
use app\modules\bot\models\User as TelegramUser;
use app\modules\bot\controllers\publics\JoinCaptchaController;

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
        $telegramChat = $this->getTelegramChat();

        $joinCaptchaStatus = $telegramChat->getSetting(ChatSetting::JOIN_CAPTCHA_STATUS);
        $role = JoinCaptchaController::ROLE_VERIFIED;

        if ($this->getUpdate()->getMessage()->getNewChatMembers()) {
            foreach ($this->getUpdate()->getMessage()->getNewChatMembers() as $newChatMember) {
                $telegramUser = TelegramUser::findOne([
                    'provider_user_id' => $newChatMember->getId()
                ]);

                if (!$telegramUser) {
                    $telegramUser = TelegramUser::createUser($newChatMember);
                    $telegramUser->updateInfo($newChatMember);
                    $telegramUser->save();
                }

                if (!$telegramChat->hasUser($telegramUser)) {
                    $telegramChatMember = $this->getBotApi()->getChatMember(
                        $telegramChat->chat_id,
                        $telegramUser->provider_user_id
                    );

                    if (isset($joinCaptchaStatus) && ($joinCaptchaStatus->value == ChatSetting::JOIN_CAPTCHA_STATUS_ON)) {
                        if (!$newChatMember->isBot()) {
                            $role = JoinCaptchaController::ROLE_UNVERIFIED;
                        }
                    }

                    $telegramChat->link('users', $telegramUser, [
                        'status' => $telegramChatMember->getStatus(),
                        'role' => $role,
                    ]);
                }
            }

            $joinHiderStatus = $telegramChat->getSetting(ChatSetting::JOIN_HIDER_STATUS);

            if (isset($joinHiderStatus) && ($joinHiderStatus->value == ChatSetting::JOIN_HIDER_STATUS_ON)) {
                try {
                    $this->getBotApi()->deleteMessage(
                        $telegramChat->chat_id,
                        $this->getUpdate()->getMessage()->getMessageId()
                    );
                } catch (HttpException $e) {
                    Yii::warning($e);
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
            $telegramChat = $this->getTelegramChat();
            $telegramUser = $this->getTelegramUser();

            $joinHiderStatus = $telegramChat->getSetting(ChatSetting::JOIN_HIDER_STATUS);

            if (isset($joinHiderStatus) && $joinHiderStatus->value == ChatSetting::JOIN_HIDER_STATUS_ON) {
                try {
                    $this->getBotApi()->deleteMessage(
                        $telegramChat->chat_id,
                        $this->getUpdate()->getMessage()->getMessageId()
                    );
                } catch (HttpException $e) {
                    Yii::warning($e);
                }
            }

            // Remove captcha info if user left channel
            $botCaptcha = BotChatCaptcha::find()
                ->where([
                    'chat_id' => $telegramChat->id,
                    'provider_user_id' => $telegramUser->provider_user_id,
                ])
                ->one();

            if (isset($botCaptcha)) {
                try {
                    $this->getBotApi()->deleteMessage(
                        $telegramChat->chat_id,
                        $botCaptcha->captcha_message_id
                    );
                } catch (HttpException $e) {
                    Yii::warning($e);
                }

                $botCaptcha->delete();
            }
        }
    }

    public function actionGroupToSupergroup()
    {
        if ($update->getMessage()->getMigrateToChatId()) {
            $telegramChat = $this->getTelegramChat();

            $telegramChat->setAttributes([
                'type' => Chat::TYPE_SUPERGROUP,
                'chat_id' => $this->getMessage()->getMigrateToChatId(),
            ]);

            $telegramChat->save();
        }
    }
}
