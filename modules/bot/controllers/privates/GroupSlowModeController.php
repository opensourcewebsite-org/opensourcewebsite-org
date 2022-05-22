<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use yii\data\Pagination;
use app\modules\bot\components\helpers\Emoji;

/**
* Class GroupSlowModeController
*
* @package app\modules\bot\controllers\privates
*/
class GroupSlowModeController extends Controller
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
                            'text' => $chat->slow_mode_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-messages-limit', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Messages limit'),
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

        switch ($chat->slow_mode_status) {
            case ChatSetting::STATUS_ON:
                $chat->slow_mode_status = ChatSetting::STATUS_OFF;

                break;
            case ChatSetting::STATUS_OFF:
                $chat->slow_mode_status = ChatSetting::STATUS_ON;

                break;
        }

        return $this->actionIndex($chatId);
    }

    public function actionSetMessagesLimit($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $this->getState()->setName(self::createRoute('set-messages-limit', [
            'chatId' => $chatId,
        ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = (int)$this->getUpdate()->getMessage()->getText()) {
                if ($chat->validateSettingValue('slow_mode_messages_limit', $text)) {
                    $chat->slow_mode_messages_limit = $text;

                    return $this->runAction('index', [
                        'chatId' => $chatId,
                    ]);
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-messages-limit'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }
}
