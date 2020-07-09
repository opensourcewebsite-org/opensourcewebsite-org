<?php

namespace app\modules\bot\controllers\publics;

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

            $needShowCaptcha = BotChatCaptcha::checkCaptcha($chat->id,$telegramUser->provider_user_id);

            if ($needShowCaptcha) {

                return $this->getResponseBuilder()
                    ->editMessageTextOrSendMessage(
                        $this->render('show-captcha', [
                            'provider_user_id' => $telegramUser->provider_user_id,
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

    public function actionPassCaptcha()
    {
        /* here will be the process of passing captcha */
    }

}
