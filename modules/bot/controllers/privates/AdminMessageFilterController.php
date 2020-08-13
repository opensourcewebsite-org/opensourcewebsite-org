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
* @package app\modules\bot\controllers\privates
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
                    'type' => ChatSetting::FILTER_MODE_BLACKLIST
                ],
                'actionGroupName' => 'blacklist',
            ])->actions(),
            Yii::createObject([
                'class' => WordlistAdminComponent::className(),
                'wordModelClass' => Phrase::className(),
                'modelAttributes' => [
                    'type' => ChatSetting::FILTER_MODE_WHITELIST
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

        $chatTitle = $chat->title;

        $statusSetting = $chat->getSetting(ChatSetting::FILTER_STATUS);
        $statusOn = ($statusSetting->value == ChatSetting::FILTER_STATUS_ON);

        $modeSetting = $chat->getSetting(ChatSetting::FILTER_MODE);
        $isModeWhitelist = ($modeSetting->value == ChatSetting::FILTER_MODE_WHITELIST);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chatTitle')),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Status') . ': ' . ($statusOn ? 'ON' : 'OFF'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('update', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Mode') . ': ' . ($isModeWhitelist ? Yii::t('bot', 'Whitelist') : Yii::t('bot', 'Blacklist')),
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
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
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

        if ($modeSetting->value == ChatSetting::FILTER_MODE_WHITELIST) {
            $modeSetting->value = ChatSetting::FILTER_MODE_BLACKLIST;
        } else {
            $modeSetting->value = ChatSetting::FILTER_MODE_WHITELIST;
        }

        $modeSetting->save();

        return $this->actionIndex($chatId);
    }

    public function actionSetStatus($chatId = null)
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
