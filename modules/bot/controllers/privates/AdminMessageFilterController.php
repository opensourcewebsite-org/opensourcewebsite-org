<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\actions\privates\wordlist\WordlistAdminComponent;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\Phrase;
use yii\data\Pagination;
use app\modules\bot\components\helpers\Emoji;

/**
* Class AdminMessageFilterController
*
* @package app\controllers\bot
*/
class AdminMessageFilterController extends Controller
{
    public function actions()
    {
        return array_merge(
            parent::actions(),
            Yii::createObject([
                'class' => WordlistAdminComponent::className(),
                'wordModelClass' => Phrase::className(),
                'modelAttributes' => [
                    'type' => Chat::FILTER_MODE_BLACKLIST
                ],
                'actionGroupName' => 'blacklist',
            ])->actions(),
            Yii::createObject([
                'class' => WordlistAdminComponent::className(),
                'wordModelClass' => Phrase::className(),
                'modelAttributes' => [
                    'type' => Chat::FILTER_MODE_WHITELIST
                ],
                'actionGroupName' => 'whitelist',
            ])->actions()
        );
    }

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

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chatTitle', 'isFilterOn', 'isFilterModeBlack')),
                [
                    [
                        [
                            'callback_data' => self::createRoute('status', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Status') . ': ' . ($isFilterOn ? 'ON' : 'OFF'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('update', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Mode') . ': ' . ($isFilterModeBlack ? Yii::t('bot', 'Blacklist') : Yii::t('bot', 'Whitelist')),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('whitelist-word-list', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Whitelist'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('blacklist-word-list', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Blacklist'),
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
            return [];
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
