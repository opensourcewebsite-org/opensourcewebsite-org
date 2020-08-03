<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class AdminJoinHiderController
 *
 * @package app\controllers\bot
 */
class AdminJoinHiderController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $chatTitle = $chat->title;

        $statusSetting = $chat->getSetting(ChatSetting::JOIN_HIDER_STATUS);
        $statusOn = ($statusSetting->value == ChatSetting::JOIN_HIDER_STATUS_ON);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chatTitle')),
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

        $statusSetting = $chat->getSetting(ChatSetting::JOIN_HIDER_STATUS);

        if ($statusSetting->value == ChatSetting::JOIN_HIDER_STATUS_ON) {
            $statusSetting->value = ChatSetting::JOIN_HIDER_STATUS_OFF;
        } else {
            $statusSetting->value = ChatSetting::JOIN_HIDER_STATUS_ON;
        }

        $statusSetting->save();

        return $this->actionIndex($chatId);
    }
}
