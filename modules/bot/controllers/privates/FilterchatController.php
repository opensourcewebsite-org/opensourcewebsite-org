<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\Setting;

/**
 * Class FilterChatController
 *
 * @package app\controllers\bot
 */
class FilterchatController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($groupId = null)
    {
        $chat = Chat::find()->where(['id' => $groupId])->one();

        $statusSetting = Setting::find()->where(['chat_id' => $groupId, 'setting' => Setting::FILTER_STATUS])->one();

        if (!isset($statusSetting)) {
            $statusSetting = new Setting();

            $statusSetting->setAttributes([
                'chat_id' => $groupId,
                'setting' => Setting::FILTER_STATUS,
                'value' => Setting::FILTER_STATUS_OFF,
            ]);

            $statusSetting->save();
        }

        $modeSetting = Setting::find()->where(['chat_id' => $groupId, 'setting' => Setting::FILTER_MODE])->one();

        if (!isset($modeSetting)) {
            $modeSetting = new Setting();

            $modeSetting->setAttributes([
                'chat_id' => $groupId,
                'setting' => Setting::FILTER_MODE,
                'value' => Setting::FILTER_MODE_BLACK,
            ]);

            $modeSetting->save();
        }

        $groupTitle = $chat->title;
        $isFilterOn = ($statusSetting->value == Setting::FILTER_STATUS_ON);
        $isFilterModeBlack = ($modeSetting->value == Setting::FILTER_MODE_BLACK);

        return [
            new EditMessageTextCommand(
                $this->getTelegramChat()->chat_id,
                $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId(),
                $this->render('index', compact('groupTitle', 'isFilterOn', 'isFilterModeBlack')),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/admin_filter_change_filter_on ' . $groupId,
                                'text' => Yii::t('bot', 'Status') . ': ' . ($isFilterOn ? "ON" : "OFF"), 
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/admin_filter_change_filter_mode ' . $groupId,
                                'text' => Yii::t('bot', 'Change mode'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/admin_filter_whitelist ' . $groupId,
                                'text' => Yii::t('bot', 'Change WhiteList'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/admin_filter_blacklist ' . $groupId,
                                'text' => Yii::t('bot', 'Change BlackList'),
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
        $modeSetting = Setting::find()->where(['chat_id' => $groupId, 'setting' => Setting::FILTER_MODE])->one();

        if ($modeSetting->value == Setting::FILTER_MODE_BLACK) {
            $modeSetting->value = Setting::FILTER_MODE_WHITE;
        } else {
            $modeSetting->value = Setting::FILTER_MODE_BLACK;
        }

        $modeSetting->save();

        return $this->actionIndex($groupId);
    }

    public function actionStatus($groupId = null)
    {
        $statusSetting = Setting::find()->where(['chat_id' => $groupId, 'setting' => Setting::FILTER_STATUS])->one();

        if ($statusSetting->value == Setting::FILTER_STATUS_ON) {
            $statusSetting->value = Setting::FILTER_STATUS_OFF;
        } else {
            $statusSetting->value = Setting::FILTER_STATUS_ON;
        }

        $statusSetting->save();

        return $this->actionIndex($groupId);
    }
}
