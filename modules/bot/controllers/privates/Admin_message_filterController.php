<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\EditMessageTextCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;

/**
 * Class Admin_message_filterChatController
 *
 * @package app\controllers\bot
 */
class Admin_message_filterController extends Controller
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

        $statusSetting = $chat->getSetting(ChatSetting::FILTER_STATUS);

        if (!isset($statusSetting)) {
            $statusSetting = new ChatSetting();

            $statusSetting->setAttributes([
                'chat_id' => $chatId,
                'setting' => ChatSetting::FILTER_STATUS,
                'value' => ChatSetting::FILTER_STATUS_OFF,
            ]);

            $statusSetting->save();
        }

        $modeSetting = $chat->getSetting(ChatSetting::FILTER_MODE);

        if (!isset($modeSetting)) {
            $modeSetting = new ChatSetting();

            $modeSetting->setAttributes([
                'chat_id' => $chatId,
                'setting' => ChatSetting::FILTER_MODE,
                'value' => ChatSetting::FILTER_MODE_BLACKLIST,
            ]);

            $modeSetting->save();
        }

        $chatTitle = $chat->title;
        $isFilterOn = ($statusSetting->value == ChatSetting::FILTER_STATUS_ON);
        $isFilterModeBlack = ($modeSetting->value == ChatSetting::FILTER_MODE_BLACKLIST);

        return [
            new EditMessageTextCommand(
                $this->getTelegramChat()->chat_id,
                $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId(),
                $this->render('index', compact('chatTitle', 'isFilterOn', 'isFilterModeBlack')),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/admin_message_filter_change_status ' . $chatId,
                                'text' => Yii::t('bot', 'Status') . ': ' . ($isFilterOn ? 'ON' : 'OFF'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/admin_message_filter_change_mode ' . $chatId,
                                'text' => Yii::t('bot', 'Change mode'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/admin_message_filter_whitelist ' . $chatId,
                                'text' => Yii::t('bot', 'Change WhiteList'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/admin_message_filter_blacklist ' . $chatId,
                                'text' => Yii::t('bot', 'Change BlackList'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/admin_chat '  . $chatId,
                                'text' => '🔙',
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

        $modeSetting = $chat->getSetting(ChatSetting::FILTER_MODE);

        if ($modeSetting->value == ChatSetting::FILTER_MODE_BLACKLIST) {
            $modeSetting->value = ChatSetting::FILTER_MODE_WHITELIST;
        } else {
            $modeSetting->value = ChatSetting::FILTER_MODE_BLACKLIST;
        }

        $modeSetting->save();

        return $this->actionIndex($chatId);
    }

    public function actionStatus($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return;
        }

        $statusSetting = $chat->getSetting(ChatSetting::FILTER_STATUS);

        if ($statusSetting->value == ChatSetting::FILTER_STATUS_ON) {
            $statusSetting->value = ChatSetting::FILTER_STATUS_OFF;
        } else {
            $statusSetting->value = ChatSetting::FILTER_STATUS_ON;
        }

        $statusSetting->save();

        return $this->actionIndex($chatId);
    }
}
