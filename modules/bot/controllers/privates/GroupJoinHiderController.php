<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class GroupJoinHiderController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupJoinHiderController extends Controller
{
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

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chat')),
                [
                        [
                            [
                                'callback_data' => self::createRoute('set-status', [
                                    'chatId' => $chatId,
                                ]),
                                'text' => $chat->join_hider_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('set-remove-join-messages', [
                                    'chatId' => $chatId,
                                ]),
                                'text' => ($chat->filter_remove_join_messages == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Remove join messages'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('set-remove-left-messages', [
                                    'chatId' => $chatId,
                                ]),
                                'text' => ($chat->filter_remove_left_messages == ChatSetting::STATUS_ON ? Emoji::STATUS_ON : Emoji::STATUS_OFF) . ' ' . Yii::t('bot', 'Remove left messages'),
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
                        ]
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

        switch ($chat->join_hider_status) {
            case ChatSetting::STATUS_ON:
                $chat->join_hider_status = ChatSetting::STATUS_OFF;

                break;
            case ChatSetting::STATUS_OFF:
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

                break;
        }

        return $this->actionIndex($chatId);
    }

    public function actionSetRemoveJoinMessages($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        switch ($chat->filter_remove_join_messages) {
            case ChatSetting::STATUS_ON:
                $chat->filter_remove_join_messages = ChatSetting::STATUS_OFF;

                break;
            case ChatSetting::STATUS_OFF:
                $chat->filter_remove_join_messages = ChatSetting::STATUS_ON;

                break;
        }

        return $this->actionIndex($chatId);
    }

    public function actionSetRemoveLeftMessages($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        switch ($chat->filter_remove_left_messages) {
            case ChatSetting::STATUS_ON:
                $chat->filter_remove_left_messages = ChatSetting::STATUS_OFF;

                break;
            case ChatSetting::STATUS_OFF:
                $chat->filter_remove_left_messages = ChatSetting::STATUS_ON;

                break;
        }

        return $this->actionIndex($chatId);
    }
}
