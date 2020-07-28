<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class AdminJoinCaptchaController
 *
 * @package app\controllers\bot
 */
class AdminJoinCaptchaController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($chatId = null)
    {
        $chat = Chat::findOne($chatId);
        $telegramUser = $this->getTelegramUser();

        if (!isset($chat)) {
            return [];
        }

        $statusSetting = $chat->getSetting(ChatSetting::JOIN_CAPTCHA_STATUS);

        if (!isset($statusSetting)) {
            $statusSetting = new ChatSetting();

            $statusSetting->setAttributes([
                'chat_id' => $chatId,
                'setting' => ChatSetting::JOIN_CAPTCHA_STATUS,
                'value' => ChatSetting::JOIN_CAPTCHA_STATUS_OFF,
            ]);

            $statusSetting->save();
        }

        $chatTitle = $chat->title;
        $statusOn = ($statusSetting->value == ChatSetting::JOIN_CAPTCHA_STATUS_ON);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chatTitle', 'telegramUser')),
                [
                        [
                            [
                                'callback_data' => self::createRoute('update', [
                                    'chatId' => $chatId,
                                ]),
                                'text' => Yii::t('bot', 'Status') . ': ' . Yii::t('bot', ($statusOn ? 'ON' : 'OFF')),
                            ],
                        ],
                        [
                            [
                                'callback_data' => AdminChatController::createRoute('index', [
                                    'chatId' => $chatId,
                                ]),
                                'text' => Emoji::BACK,
                            ],
                        ]
                    ]
            )
            ->build();
    }

    public function actionUpdate($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $statusSetting = $chat->getSetting(ChatSetting::JOIN_CAPTCHA_STATUS);

        if ($statusSetting->value == ChatSetting::JOIN_CAPTCHA_STATUS_ON) {
            $statusSetting->value = ChatSetting::JOIN_CAPTCHA_STATUS_OFF;
        } else {
            $statusSetting->value = ChatSetting::JOIN_CAPTCHA_STATUS_ON;
        }

        $statusSetting->save();

        return $this->actionIndex($chatId);
    }
}
