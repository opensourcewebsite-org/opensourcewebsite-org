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

            $needShowCaptcha = BotChatCaptcha::passedCaptcha($chat->id,$telegramUser->provider_user_id);

            $isAdmin = false;

            if (isset($chatId) && isset($telegramUser)){
                $chatMember = ChatMember::find()
                    ->select('bot_chat_member.id')
                    ->leftJoin('bot_chat','bot_chat_member.chat_id = bot_chat.id')
                    ->leftJoin('bot_user','bot_chat_member.user_id = bot_user.id')
                    ->where(['bot_chat.id' => $chat->id, 'bot_user.provider_user_id' => $telegramUser->provider_user_id])->one();
            }

            if(isset($chatMember)){
                $isAdmin = $chatMember->isAdmin();
            }

            if ($needShowCaptcha && !$isAdmin) {

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

                return $this->getResponseBuilder()
                    ->editMessageTextOrSendMessage(
                        $this->render('show-captcha', [
                            'chatName' => $chat->title,
                            'firstName' => $telegramUser->provider_user_first_name,
                            'lastName' => $telegramUser->provider_user_last_name,
                        ]),
                        [
                            [
                                [
                                    'callback_data' => self::createRoute('pass-captcha', ['provider_user_id' => $telegramUser->provider_user_id]),
                                    'text' => Yii::t('bot', 'Please click here to pass the captcha'),
                                ],
                            ],
                        ]
                    )->build();

            }
        }

        return [];
    }


    /**
     * Action allows user to pass captcha. This actions checks if joined user is interracting
     * @param integer $provider_user_id
     * @return array
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionPassCaptcha($provider_user_id)
    {
        if (isset($provider_user_id) && $this->update->getCallbackQuery()->getFrom()->getId() == $provider_user_id){

            $chat = $this->getTelegramChat();
            $telegramUser = $this->getTelegramUser();
            $chatCaptcha = BotChatCaptcha::findOne(['chat_id' => $chat->id,'provider_user_id' => $provider_user_id]);
            if(isset($chatCaptcha)){

                /* Allow user to send messages*/
                $api = $this->module->getBotApi();
                $api->call('restrictChatMember',[
                    'chat_id' => $chat->chat_id,
                    'user_id' => $telegramUser->provider_user_id,
                    'permissions' =>json_encode([
                        'can_send_messages' => true,
                        'can_send_media_messages' => true,
                        'can_send_polls' => true,
                    ]),
                ]);

                $chatCaptcha->delete();
                $toUserName = $this->update->getCallbackQuery()->getFrom()->getUsername();
                $text = new MessageText(Yii::t('bot', 'Thank you for validating,') . ', ' . $toUserName);
                return $this->getResponseBuilder()->sendMessage($text)->build();

            }

        }
        else{

            $toUserName = $this->update->getCallbackQuery()->getFrom()->getUsername();
            $text = new MessageText(Yii::t('bot', 'This captcha is not for you') . ', ' . $toUserName);
            return $this->getResponseBuilder()->sendMessage($text)->build();

        }

        return [];
    }

}
