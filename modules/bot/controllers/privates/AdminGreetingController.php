<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class AdminGreetingController
 *
 * @package app\controllers\bot
 */
class AdminGreetingController extends Controller
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

        $chatTitle = $chat->title;

        $statusSetting = $chat->getSetting(ChatSetting::GREETING_STATUS);
        $statusOn = ($statusSetting->value == ChatSetting::GREETING_STATUS_ON);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chatTitle', 'telegramUser')),
                [
                        [
                            [
                                'callback_data' => self::createRoute('set-status', [
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

    public function actionSetStatus($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $statusSetting = $chat->getSetting(ChatSetting::GREETING_STATUS);

        if ($statusSetting->value == ChatSetting::GREETING_STATUS_ON) {
            $statusSetting->value = ChatSetting::GREETING_STATUS_OFF;
        } else {
            $statusSetting->value = ChatSetting::GREETING_STATUS_ON;
        }

        $statusSetting->save();

        return $this->actionIndex($chatId);
    }
}
