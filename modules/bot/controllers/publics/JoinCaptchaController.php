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
                $chatMember = ChatMember::find()
                    ->select('bot_chat_member.id,bot_chat_member.passed_captcha')
                    ->leftJoin('bot_chat','bot_chat_member.chat_id = bot_chat.id')
                    ->leftJoin('bot_user','bot_chat_member.user_id = bot_user.id')
                    ->where(['bot_chat.id' => $chat->id, 'bot_user.provider_user_id' => $telegramUser->provider_user_id])->one();
            }

            if(isset($chatMember)){
                $isAdmin = $chatMember->isAdmin();
                $passedCaptcha = $chatMember->passed_captcha;
            }

            if (!$passedCaptcha && !$isAdmin) {

                $botCaptcha = BotChatCaptcha::find()->where([
                    'chat_id' => $chat->id,
                    'provider_user_id' => $telegramUser->provider_user_id
                ])->exists();

                if (!$botCaptcha) {

                    $botCaptcha = new BotChatCaptcha([
                        'chat_id' => $chat->id,
                        'provider_user_id' => $telegramUser->provider_user_id,
                    ]);

                    $botCaptcha->save();
                }

                /* Restrict users to write messages*/
                $api = $this->module->getBotApi();
                $api->call('restrictChatMember',[
                    'chat_id' => $chat->chat_id,
                    'user_id' => $telegramUser->provider_user_id,
                    'permissions' =>json_encode([
                        'can_send_messages' => false,
                        'can_send_media_messages' => false,
                        'can_send_polls' => false,
                    ]),
                ]);

                $choices = [
                    [
                        'callback_data' => self::createRoute('pass-captcha', [
                            'provider_user_id' => $telegramUser->provider_user_id,
                            'choice' => self::PASS
                        ]),
                        'text' => Yii::t('bot', 'Pass'),
                    ],
                    [
                        'callback_data' => self::createRoute('pass-captcha', ['provider_user_id' => $telegramUser->provider_user_id ,'choice' => self::DUMMY]),
                        'text' => Yii::t('bot', 'Dummy'),
                    ],
                    [
                        'callback_data' => self::createRoute('pass-captcha', ['provider_user_id' => $telegramUser->provider_user_id, 'choice' => self::BAN]),
                        'text' => Yii::t('bot', 'Ban'),
                    ],
                ];
                shuffle($choices);
                return $this->getResponseBuilder()
                    ->editMessageTextOrSendMessage(
                        $this->render('show-captcha', [
                            'chatName' => $chat->title,
                            'firstName' => $telegramUser->provider_user_first_name,
                            'lastName' => $telegramUser->provider_user_last_name,
                            'provider_user_name' => $telegramUser->provider_user_name
                        ]),
                        [
                            $choices
                        ]
                    )->build();

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
            $api = $this->module->getBotApi();
            $toUserName = $this->update->getCallbackQuery()->getFrom()->getUsername();

            switch ($choice){

                case self::BAN:

                    BotChatCaptcha::removeCaptchaInfo($chat->id,$telegramUser->provider_user_id);

                    $api->kickChatMember($chat->chat_id,$telegramUser->provider_user_id,time() + self::BAN_DURATION);

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
                    if(!$chatMember->passed_captcha){

                        /* Allow user to send messages*/
                        $api->call('restrictChatMember',[
                            'chat_id' => $chat->chat_id,
                            'user_id' => $telegramUser->provider_user_id,
                            'permissions' =>json_encode([
                                'can_send_messages' => true,
                                'can_send_media_messages' => true,
                                'can_send_polls' => true,
                            ]),
                        ]);

                        // Delete record about captcha
                        BotChatCaptcha::deleteAll([
                            'chat_id' => $chat->id,
                            'provider_user_id' => $provider_user_id
                        ]);

                        // Set status passed_captcha = 1 in bot_chat_member table
                        $chatMember->passed_captcha = 1;
                        $chatMember->save();

                        $text = new MessageText(Yii::t('bot', 'Thank you for validating,') . ', ' . $toUserName);

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

}
