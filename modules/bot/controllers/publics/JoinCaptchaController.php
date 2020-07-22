<?php

namespace app\modules\bot\controllers\publics;

use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\models\BotChatCaptcha;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use Yii;
use app\modules\bot\components\Controller;

/**
 * Class JoinCaptchaController
 *
 * @package app\controllers\bot
 */
class JoinCaptchaController extends Controller
{

    public const PASS = 1;
    public const BAN = 2;
    public const DUMMY = 3;

    public const ROLE_UNVERIFIED = 0;
    public const ROLE_VERIFIED = 1;

    public const BAN_DURATION = 365*24*60*60;

    /**
     * Action shows captcha and restricts user to send messages
     *
     * @return array
     */
    public function actionShowCaptcha()
    {
        $chat = $this->getTelegramChat();
        $joinCaptchaStatus = $chat->getSetting(ChatSetting::JOIN_CAPTCHA_STATUS);

        if (isset($joinCaptchaStatus) && $joinCaptchaStatus->value == ChatSetting::JOIN_CAPTCHA_STATUS_ON) {

            $telegramUser = $this->getTelegramUser();

            $isAdmin = false;

            if (isset($chat->id) && isset($telegramUser)){
                $chatMember = ChatMember::findOne(['chat_id' => $chat->id, 'user_id' => $telegramUser->id ]);
            }

            if(isset($chatMember)){
                $isAdmin = $chatMember->isAdmin();
                $passedCaptcha = $chatMember->role == self::ROLE_VERIFIED;
            }

            if (!$passedCaptcha && !$isAdmin) {

                $choices = [
                    [
                        'callback_data' => self::createRoute('pass-captcha', [
                            'provider_user_id' => $telegramUser->provider_user_id,
                            'choice' => self::PASS
                        ]),
                        'text' => Yii::t('bot', '👍'),
                    ],
                    [
                        'callback_data' => self::createRoute('pass-captcha', ['provider_user_id' => $telegramUser->provider_user_id ,'choice' => self::DUMMY]),
                        'text' => Yii::t('bot', '👌'),
                    ],
                    [
                        'callback_data' => self::createRoute('pass-captcha', ['provider_user_id' => $telegramUser->provider_user_id, 'choice' => self::BAN]),
                        'text' => Yii::t('bot', '👎'),
                    ],
                ];
                shuffle($choices);

                $command =  $this->getResponseBuilder()
                    ->sendMessage(
                        $this->render('show-captcha', [
                            'user' => $telegramUser
                        ]),
                        [
                            $choices
                        ]
                    )->build();

                $response = $this->send($command);

                if($response){

                    $botCaptcha = BotChatCaptcha::find()->where([
                        'chat_id' => $chat->id,
                        'provider_user_id' => $telegramUser->provider_user_id
                    ])->exists();

                    if (!$botCaptcha) {

                        $botCaptcha = new BotChatCaptcha([
                            'chat_id' => $chat->id,
                            'provider_user_id' => $telegramUser->provider_user_id,
                            'captcha_message_id' => $response->getMessageId()
                        ]);

                        $botCaptcha->save();
                    }

                }
                return [];
            }
        }

        return [];
    }


    /**
     * Action allows user to pass captcha. This actions checks if joined user is interracting.
     *
     * @param integer $provider_user_id
     * @param integer $choice
     * @return array
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionPassCaptcha($provider_user_id, $choice)
    {
        if (isset($provider_user_id) && $this->update->getCallbackQuery()->getFrom()->getId() == $provider_user_id){

            $chat = $this->getTelegramChat();
            $telegramUser = $this->getTelegramUser();
            $api = $this->getBotApi();
            $toUserName = $this->update->getCallbackQuery()->getFrom()->getUsername();

            $botCaptcha = BotChatCaptcha::find()->where([
                'chat_id' => $chat->id,
                'provider_user_id' => $telegramUser->provider_user_id
            ])->one();
            if (isset($botCaptcha)){
                $captchaMessageId = $botCaptcha->captcha_message_id;
            }

            switch ($choice){

                case self::BAN:

                    BotChatCaptcha::removeCaptchaInfo($chat->id,$telegramUser->provider_user_id);

                    //kick member
                    $api->kickChatMember($chat->chat_id,$telegramUser->provider_user_id,time() + self::BAN_DURATION);

                    //remove captcha message
                    $api->deleteMessage($chat->chat_id, $captchaMessageId);

                    $text = new MessageText(Yii::t('bot', 'User: {provider_user_name} was banned in chat: {chat_title} for: {ban_duration} minutes',[
                        'provider_user_name' => $telegramUser->provider_user_name,
                        'chat_title' => $chat->title,
                        'ban_duration' => self::BAN_DURATION
                        ]));
                    break;

                case self::DUMMY:

                    return [];

                    break;

                case self::PASS:

                    $chatMember = ChatMember::findOne([
                        'chat_id' => $chat->id,
                        'user_id' => $telegramUser->id
                    ]);
                    if($chatMember->role == self::ROLE_UNVERIFIED){

                        // Delete record about captcha
                        BotChatCaptcha::removeCaptchaInfo($chat->id,$provider_user_id);

                        //remove captcha message
                        $api->deleteMessage($chat->chat_id, $captchaMessageId);

                        // Set role = 1 in bot_chat_member table
                        $chatMember->role = self::ROLE_VERIFIED;
                        $chatMember->save();

                        $text = new MessageText(Yii::t('bot', 'Thank you for validating,') . ', ' . $toUserName . '🔥');

                    }
                    else{
                        return [];
                    }
                    break;

                default:

                    return [];

                    break;
            }



        }
        else{

            $toUserName = $this->update->getCallbackQuery()->getFrom()->getUsername();
            $text = new MessageText(Yii::t('bot', 'This captcha is not for you') . ', ' . $toUserName);

        }
        return $this->getResponseBuilder()->sendMessage($text)->build();
    }


    private function send(array $messageCommand)
    {
        if(isset($messageCommand)) {
            $command = reset($messageCommand);
            $response = $command->send($this->getBotApi());
            return $response;
        }
        return false;
    }
}
