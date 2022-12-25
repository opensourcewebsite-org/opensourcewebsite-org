<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\actions\privates\wordlist\WordlistComponent;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\ChatPhrase;
use Yii;
use yii\data\Pagination;

/**
* Class GroupMessageFilterController
*
* @package app\modules\bot\controllers\privates
*/
class GroupMessageFilterController extends Controller
{
    protected static $statuses = [
        0 => 'filter_status',
        1 => 'filter_remove_reply',
        2 => 'filter_remove_username',
        3 => 'filter_remove_emoji',
        4 => 'filter_remove_empty_line',
        5 => 'filter_remove_channels',
        6 => 'filter_remove_styled_texts',
    ];

    public function actions()
    {
        return array_merge(
            parent::actions(),
            Yii::createObject([
                'class' => WordlistComponent::class,
                'wordModelClass' => ChatPhrase::class,
                'modelAttributes' => [
                    'type' =>ChatPhrase::TYPE_BLACKLIST,
                ],
                'actionGroupName' =>ChatPhrase::TYPE_BLACKLIST,
            ])->actions(),
            Yii::createObject([
                'class' => WordlistComponent::class,
                'wordModelClass' => ChatPhrase::class,
                'modelAttributes' => [
                    'type' =>ChatPhrase::TYPE_WHITELIST,
                ],
                'actionGroupName' =>ChatPhrase::TYPE_WHITELIST,
            ])->actions()
        );
    }

    /**
    * @param int|null $id Chat->id
    * @return array
    */
    public function actionIndex($id = null)
    {
        $chat = Chat::findOne($id);

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
                                'id' => $chat->id,
                            ]),
                            'text' => $chat->filter_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-mode', [
                                'id' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Mode') . ': ' . $chat->getFilterModeLabel(),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('whitelist-word-list', [
                                'chatId' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Whitelist'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('blacklist-word-list', [
                                'chatId' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Blacklist'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'id' => $chat->id,
                                'i' => 1,
                            ]),
                            'text' => ($chat->filter_remove_reply == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Remove: reply'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'id' => $chat->id,
                                'i' => 2,
                            ]),
                            'text' => ($chat->filter_remove_username == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Remove: username'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'id' => $chat->id,
                                'i' => 3,
                            ]),
                            'text' => ($chat->filter_remove_emoji == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Remove: emoji'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'id' => $chat->id,
                                'i' => 4,
                            ]),
                            'text' => ($chat->filter_remove_empty_line == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Remove: empty line'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'id' => $chat->id,
                                'i' => 5,
                            ]),
                            'text' => ($chat->filter_remove_channels == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Remove: channels'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'id' => $chat->id,
                                'i' => 6,
                            ]),
                            'text' => ($chat->filter_remove_styled_texts == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Remove: styled texts'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupController::createRoute('view', [
                                'chatId' => $chat->id,
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

    /**
     * @param int|null $id Chat->id
     * @param int $i $this->statuses[]
     * @return array
     */
    public function actionSetStatus($id = null, $i = 0)
    {
        if (!isset(static::$statuses[$i])) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $status = static::$statuses[$i];

        switch ($chat->{$status}) {
            case ChatSetting::STATUS_ON:
                $chat->{$status} = ChatSetting::STATUS_OFF;

                break;
            case ChatSetting::STATUS_OFF:
                if ($status == 'filter_status') {
                    $chatMember = $chat->getChatMemberByUserId();

                    if (!$chatMember->trySetChatSetting('filter_status', ChatSetting::STATUS_ON)) {
                        return $this->getResponseBuilder()
                            ->answerCallbackQuery(
                                $this->render('alert-status-on', [
                                    'requiredRating' => $chatMember->getRequiredRatingForChatSetting('filter_status', ChatSetting::STATUS_ON),
                                ]),
                                true
                            )
                            ->build();
                    }
                } else {
                    $chat->{$status} = ChatSetting::STATUS_ON;
                }

                break;
        }

        return $this->actionIndex($chat->id);
    }

    /**
     * @param int|null $id Chat->id
     * @return array
     */
    public function actionSetMode($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
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

        return $this->actionIndex($chat->id);
    }
}
