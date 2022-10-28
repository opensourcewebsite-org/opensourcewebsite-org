<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\models\ChatCaptcha;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use Yii;

/**
 * Class JoinCaptchaController
 *
 * @package app\modules\bot\controllers\groups
 */
class JoinCaptchaController extends Controller
{
    public const PASS = 1;
    public const BAN = 2;
    public const DUMMY = 3;

    public const ROLE_UNVERIFIED = 0;
    public const ROLE_VERIFIED = 1;

    /**
     * Action shows captcha
     *
     * @return array
     */
    public function actionShowCaptcha()
    {
        $telegramUser = $this->getTelegramUser();
        $chat = $this->getTelegramChat();

        if ($chat->isJoinCaptchaOn() && !$telegramUser->captcha_confirmed_at) {
            $chatMember = ChatMember::findOne([
                'chat_id' => $chat->id,
                'user_id' => $telegramUser->id,
            ]);

            if ($chatMember->role == self::ROLE_UNVERIFIED) {
                $buttons = [
                    [
                        'callback_data' => self::createRoute('pass-captcha', [
                            'provider_user_id' => $telegramUser->provider_user_id,
                            'choice' => self::PASS,
                        ]),
                        'text' => '👍',
                    ],
                    [
                        'callback_data' => self::createRoute('pass-captcha', [
                            'provider_user_id' => $telegramUser->provider_user_id,
                            'choice' => self::DUMMY,
                        ]),
                        'text' => '👌',
                    ],
                    [
                        'callback_data' => self::createRoute('pass-captcha', [
                            'provider_user_id' => $telegramUser->provider_user_id,
                            'choice' => self::BAN,
                        ]),
                        'text' => '👎',
                    ],
                ];
                shuffle($buttons);

                $response =  $this->getResponseBuilder()
                    ->sendMessage(
                        $this->render('show-captcha', [
                            'user' => $telegramUser,
                        ]),
                        [
                            $buttons,
                        ],
                        [
                            'disableNotification' => true,
                        ]
                    )
                    ->send();

                if ($response) {
                    $captcha = ChatCaptcha::find()
                        ->where([
                            'chat_id' => $chat->id,
                            'provider_user_id' => $telegramUser->provider_user_id,
                        ])
                        ->exists();

                    if (!$captcha) {
                        $captcha = new ChatCaptcha([
                            'chat_id' => $chat->id,
                            'provider_user_id' => $telegramUser->provider_user_id,
                            'captcha_message_id' => $response->getMessageId(),
                        ]);

                        $captcha->save();
                    }
                }
            }
        }
    }

    /**
     * Action allows user to pass captcha. This actions checks if joined user is interracting.
     *
     * @param integer $provider_user_id
     * @param integer $choice
     * @return boolean
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionPassCaptcha($provider_user_id, $choice)
    {
        if (isset($provider_user_id) && ($this->getUpdate()->getCallbackQuery()->getFrom()->getId() == $provider_user_id)) {
            $chat = $this->getTelegramChat();
            $telegramUser = $this->getTelegramUser();

            $captcha = ChatCaptcha::find()
                ->where([
                    'chat_id' => $chat->id,
                    'provider_user_id' => $telegramUser->provider_user_id,
                ])
                ->one();

            if (isset($captcha)) {
                switch ($choice) {
                    case self::PASS:
                        $chatMember = ChatMember::findOne([
                            'chat_id' => $chat->id,
                            'user_id' => $telegramUser->id,
                        ]);

                        if ($chatMember->role == self::ROLE_UNVERIFIED) {
                            // Remove captcha message

                            $this->getBotApi()->deleteMessage($chat->chat_id, $captcha->captcha_message_id);

                            // Delete record about captcha
                            $captcha->delete();

                            // Set role = 1 in bot_chat_member table
                            $chatMember->role = self::ROLE_VERIFIED;
                            $chatMember->save(false);

                            $telegramUser->captcha_confirmed_at = time();
                            $telegramUser->save(false);
                        } else {
                            return false;
                        }

                        break;
                    case self::BAN:
                        // Kick member from the group
                        $this->getBotApi()->kickChatMember(
                            $chat->chat_id,
                            $telegramUser->provider_user_id
                        );

                        // Remove captcha message
                        $this->getBotApi()->deleteMessage($chat->chat_id, $captcha->captcha_message_id);

                        // Delete record about captcha
                        $captcha->delete();

                        $telegramUser->captcha_confirmed_at = null;
                        $telegramUser->save(false);

                        break;
                    default:
                        return false;

                        break;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }
}
