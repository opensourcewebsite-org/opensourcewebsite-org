<?php

namespace app\modules\bot\controllers\publics;

use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\models\BotChatCaptcha;
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
     * Action shows captcha
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

            if ($needShowCaptcha) {

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

    }


    /**
     * Action allows user to pass captcha. This actions checks if joined user is interracting
     *
     * @return array
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionPassCaptcha($provider_user_id = null)
    {
        if (isset($provider_user_id) && $this->update->getCallbackQuery()->getFrom()->getId() == $provider_user_id){

            $chatId = $this->getTelegramChat()->id;
            $chatCaptcha = BotChatCaptcha::findOne(['chat_id' => $chatId,'provider_user_id' => $provider_user_id]);
            if(isset($chatCaptcha)){

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
    }

}
