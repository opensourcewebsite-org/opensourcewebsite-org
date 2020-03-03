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
class Join_hider_mainController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($groupId = null)
    {
        $chat = Chat::find()->where(['id' => $groupId])->one();

        $statusSetting = ChatSetting::find()->where(['chat_id' => $groupId, 'setting' => ChatSetting::JOIN_HIDER_STATUS])->one();

        if (!isset($statusSetting)) {
            $statusSetting = new ChatSetting();

            $statusSetting->setAttributes([
                'chat_id' => $groupId,
                'setting' => ChatSetting::JOIN_HIDER_STATUS,
                'value' => ChatSetting::JOIN_HIDER_STATUS_OFF,
            ]);

            $statusSetting->save();
        }

        $groupTitle = $chat->title;
        $statusOn = ($statusSetting->value == ChatSetting::JOIN_HIDER_STATUS_ON);

        return [
            new EditMessageTextCommand(
                $this->getTelegramChat()->chat_id,
                $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId(),
                $this->render('index', compact('groupTitle')),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/join_hider_change_status ' . $groupId,
                                'text' => Yii::t('bot', 'Status') . ': ' . ($statusOn ? "ON" : "OFF"), 
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/admin_filter_chat '  . $groupId,
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

    public function actionUpdate($groupId = null)
    {
        $modeSetting = ChatSetting::find()->where(['chat_id' => $groupId, 'setting' => ChatSetting::JOIN_HIDER_STATUS])->one();

        if ($modeSetting->value == ChatSetting::JOIN_HIDER_STATUS_ON) {
            $modeSetting->value = ChatSetting::JOIN_HIDER_STATUS_OFF;
        } else {
            $modeSetting->value = ChatSetting::JOIN_HIDER_STATUS_ON;
        }

        $modeSetting->save();

        return $this->actionIndex($groupId);
    }
}
