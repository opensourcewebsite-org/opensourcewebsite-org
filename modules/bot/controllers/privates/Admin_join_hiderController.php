<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;

/**
 * Class FilterChatController
 *
 * @package app\controllers\bot
 */
class Admin_join_hiderController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return;
        }

        $statusSetting = $chat->getSetting(ChatSetting::JOIN_HIDER_STATUS);

        if (!isset($statusSetting)) {
            $statusSetting = new ChatSetting();

            $statusSetting->setAttributes([
                'chat_id' => $chatId,
                'setting' => ChatSetting::JOIN_HIDER_STATUS,
                'value' => ChatSetting::JOIN_HIDER_STATUS_OFF,
            ]);

            $statusSetting->save();
        }

        $chatTitle = $chat->title;
        $statusOn = ($statusSetting->value == ChatSetting::JOIN_HIDER_STATUS_ON);

        return [
            new EditMessageTextCommand(
                $this->getTelegramChat()->chat_id,
                $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId(),
                $this->render('index', compact('chatTitle')),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/admin_join_hider_change_status ' . $chatId,
                                'text' => Yii::t('bot', 'Status') . ': ' . ($statusOn ? "ON" : "OFF"), 
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/admin_filter_chat '  . $chatId,
                                'text' => '🔙',
                            ],
                            [
                                'callback_data' => '/menu',
                                'text' => '⏪ ' . Yii::t('bot', 'Main menu'),
                            ],
                        ]
                    ]),
                ]
            ),
        ];
    }

    public function actionUpdate($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return;
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
