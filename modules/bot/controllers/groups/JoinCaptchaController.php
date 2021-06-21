<?php

namespace app\modules\bot\controllers\groups;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\models\BotChatCaptcha;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;

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
        $chat = $this->getTelegramChat();

        if ($chat->join_captcha_status == ChatSetting::STATUS_ON) {
            $telegramUser = $this->getTelegramUser();

            $chatMember = ChatMember::findOne([
                'chat_id' => $chat->id,
                'user_id' => $telegramUser->id,
            ]);

            if (($chatMember->role == self::ROLE_UNVERIFIED)) {
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
                    $botCaptcha = BotChatCaptcha::find()
                        ->where([
                            'chat_id' => $chat->id,
                            'provider_user_id' => $telegramUser->provider_user_id,
                        ])
                        ->exists();

                    if (!$botCaptcha) {
                        $botCaptcha = new BotChatCaptcha([
                            'chat_id' => $chat->id,
                            'provider_user_id' => $telegramUser->provider_user_id,
                            'captcha_message_id' => $response->getMessageId(),
                        ]);
                        $botCaptcha->save();
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

            $botCaptcha = BotChatCaptcha::find()
                ->where([
                    'chat_id' => $chat->id,
                    'provider_user_id' => $telegramUser->provider_user_id,
                ])
                ->one();

            if (isset($botCaptcha)) {
                $captchaMessageId = $botCaptcha->captcha_message_id;

                switch ($choice) {
                    case self::PASS:
                        $chatMember = ChatMember::findOne([
                            'chat_id' => $chat->id,
                            'user_id' => $telegramUser->id,
                        ]);

                        if ($chatMember->role == self::ROLE_UNVERIFIED) {
                            // Remove captcha message

                            $this->getBotApi()->deleteMessage($chat->chat_id, $captchaMessageId);

                            // Delete record about captcha
                            $botCaptcha->delete();

                            // Set role = 1 in bot_chat_member table
                            $chatMember->role = self::ROLE_VERIFIED;
                            $chatMember->save();
                        } else {
                            return false;
                        }
                        break;
                    case self::BAN:
                        // Kick member from the group
                        $this->getBotApi()
                            ->kickChatMember(
                                $chat->chat_id,
                                $telegramUser->provider_user_id
                            );

                        // Remove captcha message
                        $this->getBotApi()
                            ->deleteMessage(
                                $chat->chat_id,
                                $captchaMessageId
                            );

                        // Delete record about captcha
                        $botCaptcha->delete();
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
