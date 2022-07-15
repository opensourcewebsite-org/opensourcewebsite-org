<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use Yii;

/**
 * Class GroupJoinHiderController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupJoinHiderController extends Controller
{
    protected static $statuses = [
        0 => 'join_hider_status',
        1 => 'filter_remove_member_joined',
        2 => 'filter_remove_member_left',
        3 => 'filter_remove_video_chat_scheduled',
        4 => 'filter_remove_video_chat_started',
        5 => 'filter_remove_video_chat_ended',
        6 => 'filter_remove_video_chat_invited',
    ];

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

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chat')),
                [
                        [
                            [
                                'callback_data' => self::createRoute('set-status', [
                                    'id' => $id,
                                ]),
                                'text' => $chat->join_hider_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('set-status', [
                                    'id' => $id,
                                    'i' => 1,
                                ]),
                                'text' => ($chat->filter_remove_member_joined == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Remove: member joined'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('set-status', [
                                    'id' => $id,
                                    'i' => 2,
                                ]),
                                'text' => ($chat->filter_remove_member_left == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Remove: member left'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('set-status', [
                                    'id' => $id,
                                    'i' => 3,
                                ]),
                                'text' => ($chat->filter_remove_video_chat_scheduled == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Remove: video chat scheduled'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('set-status', [
                                    'id' => $id,
                                    'i' => 4,
                                ]),
                                'text' => ($chat->filter_remove_video_chat_started == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Remove: video chat started'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('set-status', [
                                    'id' => $id,
                                    'i' => 5,
                                ]),
                                'text' => ($chat->filter_remove_video_chat_ended == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Remove: video chat ended'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('set-status', [
                                    'id' => $id,
                                    'i' => 6,
                                ]),
                                'text' => ($chat->filter_remove_video_chat_invited == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Remove: video chat invited'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => GroupController::createRoute('view', [
                                    'chatId' => $id,
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

        if (!isset($chat)) {
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
                if ($status == 'join_hider_status') {
                    $chatMember = $chat->getChatMemberByUserId();

                    if (!$chatMember->trySetChatSetting('join_hider_status', ChatSetting::STATUS_ON)) {
                        return $this->getResponseBuilder()
                            ->answerCallbackQuery(
                                $this->render('alert-status-on', [
                                    'requiredRating' => $chatMember->getRequiredRatingForChatSetting('join_hider_status', ChatSetting::STATUS_ON),
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

        return $this->actionIndex($id);
    }
}
