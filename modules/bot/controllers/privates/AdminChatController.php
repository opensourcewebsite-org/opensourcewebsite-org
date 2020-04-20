<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\response\commands\SendMessageCommand;
use app\modules\bot\components\response\commands\EditMessageTextCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class AdminChatController
 *
 * @package app\controllers\bot
 */
class AdminChatController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($chatId = null)
    {
        if ($chatId) {
            $chat = Chat::findOne($chatId);

            if (!isset($chat)) {
                return [];
            }

            $chatTitle = $chat->title;

            // TODO refactoring
            if ($this->getUpdate()->getCallbackQuery()) {
                return [
                    new EditMessageTextCommand(
                        $this->getTelegramChat()->chat_id,
                        $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId(),
                        $this->render('index', compact('chatTitle')),
                        [
                            'parseMode' => $this->textFormat,
                            'replyMarkup' => new InlineKeyboardMarkup([
                                [
                                    [
                                        'callback_data' => AdminJoinHiderController::createRoute('index', [
                                            'chatId' => $chatId,
                                        ]),
                                        'text' => Yii::t('bot', 'Join Hider'),
                                    ],
                                ],
                                [
                                    [
                                        'callback_data' => AdminMessageFilterController::createRoute('index', [
                                            'chatId' => $chatId,
                                        ]),
                                        'text' => Yii::t('bot', 'Message Filter'),
                                    ],
                                ],
                                [
                                    [
                                        'callback_data' => AdminVoteBanController::createRoute('index', [
                                            'chatId' => $chatId,
                                        ]),
                                        'text' => Yii::t('bot', 'Vote Ban'),
                                    ],
                                ],
                                [
                                    [
                                        'callback_data' => AdminStarTopController::createRoute('index', [
                                            'chatId' => $chatId,
                                        ]),
                                        'text' => '🏗 ' . Yii::t('bot', 'Star Top'),
                                    ],
                                ],
                                [
                                    [
                                        'callback_data' => AdminController::createRoute(),
                                        'text' => '🔙',
                                    ],
                                    [
                                        'callback_data' => MenuController::createRoute(),
                                        'text' => Emoji::MENU,
                                    ],
                                    [
                                        'callback_data' => self::createRoute('refresh', [
                                            'chatId' => $chatId,
                                        ]),
                                        'text' => '🔄',
                                    ],
                                ],
                            ]),
                        ]
                    ),
                ];
            } else {
                return [
                    new SendMessageCommand(
                        $this->getTelegramChat()->chat_id,
                        $this->render('index', compact('chatTitle')),
                        [
                            'parseMode' => $this->textFormat,
                            'replyMarkup' => new InlineKeyboardMarkup([
                                [
                                    [
                                        'callback_data' => AdminMessageFilterController::createRoute('index', [
                                            'chatId' => $chatId,
                                        ]),
                                        'text' => Yii::t('bot', 'Message Filter'),
                                    ],
                                ],
                                [
                                    [
                                        'callback_data' => AdminJoinHiderController::createRoute('index', [
                                            'chatId' => $chatId,
                                        ]),
                                        'text' => Yii::t('bot', 'Join Hider'),
                                    ],
                                ],
                                [
                                    [
                                        'callback_data' => AdminController::createRoute(),
                                        'text' => '🔙',
                                    ],
                                ],
                            ]),
                        ]
                    ),
                ];
            }
        }
    }

    /**
     * @return array
     */
    public function actionRefresh($chatId = null)
    {
        // TODO add refresh for selected group
        if ($chatId) {
            $chat = Chat::findOne($chatId);

            if (!isset($chat)) {
                return [];
            }
        }
    }
}
