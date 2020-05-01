<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;

/**
 * Class AdminStarTopController
 *
 * @package app\controllers\bot
 */
class AdminStarTopController extends Controller
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

        $statusSetting = $chat->getSetting(ChatSetting::STAR_TOP_STATUS);

        if (!isset($statusSetting)) {
            $statusSetting = new ChatSetting();

            $statusSetting->setAttributes([
                'chat_id' => $chatId,
                'setting' => ChatSetting::STAR_TOP_STATUS,
                'value' => ChatSetting::STAR_TOP_STATUS_OFF,
            ]);

            $statusSetting->save();
        }

        $chatTitle = $chat->title;
        $statusOn = ($statusSetting->value == ChatSetting::STAR_TOP_STATUS_ON);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chatTitle')),
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
                                'text' => '🔙',
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

        $statusSetting = $chat->getSetting(ChatSetting::STAR_TOP_STATUS);

        if ($statusSetting->value == ChatSetting::STAR_TOP_STATUS_ON) {
            $statusSetting->value = ChatSetting::STAR_TOP_STATUS_OFF;
        } else {
            $statusSetting->value = ChatSetting::STAR_TOP_STATUS_ON;
        }

        $statusSetting->save();

        return $this->actionIndex($chatId);
    }
}
