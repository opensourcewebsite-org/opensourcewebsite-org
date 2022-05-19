<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\actions\privates\wordlist\WordlistComponent;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\Phrase;
use yii\data\Pagination;
use app\modules\bot\components\helpers\Emoji;

/**
* Class GroupMessageFilterController
*
* @package app\modules\bot\controllers\privates
*/
class GroupMessageFilterController extends Controller
{
    public function actions()
    {
        return array_merge(
            parent::actions(),
            Yii::createObject([
                'class' => WordlistComponent::class,
                'wordModelClass' => Phrase::class,
                'modelAttributes' => [
                    'type' => Phrase::TYPE_BLACKLIST,
                ],
                'actionGroupName' => Phrase::TYPE_BLACKLIST,
            ])->actions(),
            Yii::createObject([
                'class' => WordlistComponent::class,
                'wordModelClass' => Phrase::class,
                'modelAttributes' => [
                    'type' => Phrase::TYPE_WHITELIST,
                ],
                'actionGroupName' => Phrase::TYPE_WHITELIST,
            ])->actions()
        );
    }

    /**
    * @return array
    */
    public function actionIndex($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chat')),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'chatId' => $chatId,
                            ]),
                            'text' => $chat->filter_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-mode', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Mode') . ': ' . $chat->getFilterModeLabel(),
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
                            'callback_data' => self::createRoute('set-remove-reply', [
                                'chatId' => $chatId,
                            ]),
                            'text' => ($chat->filter_remove_reply == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Remove reply'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-remove-username', [
                                'chatId' => $chatId,
                            ]),
                            'text' => ($chat->filter_remove_username == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Remove username'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-remove-emoji', [
                                'chatId' => $chatId,
                            ]),
                            'text' => ($chat->filter_remove_emoji == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Remove emoji'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-remove-empty-line', [
                                'chatId' => $chatId,
                            ]),
                            'text' => ($chat->filter_remove_empty_line == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Remove empty line'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupController::createRoute('view', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ],
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

        if ($chat->filter_status == ChatSetting::STATUS_ON) {
            $chat->filter_status = ChatSetting::STATUS_OFF;
        } else {
            $chat->filter_status = ChatSetting::STATUS_ON;
        }

        return $this->actionIndex($chatId);
    }

    public function actionSetMode($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        switch ($chat->filter_mode) {
            case ChatSetting::FILTER_MODE_OFF:
                $chat->filter_mode = ChatSetting::FILTER_MODE_BLACKLIST;

                break;
            case ChatSetting::FILTER_MODE_BLACKLIST:
                $chat->filter_mode = ChatSetting::FILTER_MODE_WHITELIST;

                break;
            case ChatSetting::FILTER_MODE_WHITELIST:
                $chat->filter_mode = ChatSetting::FILTER_MODE_OFF;

                break;
        }

        return $this->actionIndex($chatId);
    }

    public function actionSetRemoveReply($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        if ($chat->filter_remove_reply == ChatSetting::STATUS_ON) {
            $chat->filter_remove_reply = ChatSetting::STATUS_OFF;
        } else {
            $chat->filter_remove_reply = ChatSetting::STATUS_ON;
        }

        return $this->actionIndex($chatId);
    }

    public function actionSetRemoveUsername($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        if ($chat->filter_remove_username == ChatSetting::STATUS_ON) {
            $chat->filter_remove_username = ChatSetting::STATUS_OFF;
        } else {
            $chat->filter_remove_username = ChatSetting::STATUS_ON;
        }

        return $this->actionIndex($chatId);
    }

    public function actionSetRemoveEmoji($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        if ($chat->filter_remove_emoji == ChatSetting::STATUS_ON) {
            $chat->filter_remove_emoji = ChatSetting::STATUS_OFF;
        } else {
            $chat->filter_remove_emoji = ChatSetting::STATUS_ON;
        }

        return $this->actionIndex($chatId);

        return $this->actionIndex($chatId);
    }

    public function actionSetRemoveEmptyLine($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        if ($chat->filter_remove_empty_line == ChatSetting::STATUS_ON) {
            $chat->filter_remove_empty_line = ChatSetting::STATUS_OFF;
        } else {
            $chat->filter_remove_empty_line = ChatSetting::STATUS_ON;
        }

        return $this->actionIndex($chatId);
    }
}
