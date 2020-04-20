<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\response\commands\SendMessageCommand;
use app\modules\bot\components\response\commands\EditMessageTextCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\User;

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
        if ($chatId) {
            $chat = Chat::findOne($chatId);

            if (!isset($chat)) {
                return [];
            }
        }

        $update = $this->getUpdate();
        $currentUser = $this->getTelegramUser();
        $currentUserIsAdmin = false;
        $telegramAdministrators = $this->getBotApi()->getChatAdministrators($chat->chat_id);

        $administratorUsers = [];

        $currentAdministrators = $chat->getAdministrators()->all();
        foreach ($telegramAdministrators as $telegramAdministrator) {
            if ($currentUser->provider_user_id == $telegramAdministrator->getUser()->getId()) {
                $currentUserIsAdmin = true;
            }
            $user = User::findOne(['provider_user_id' => $telegramAdministrator->getUser()->getId()]);

            if (!isset($user)) {
                $user = User::createUser($telegramAdministrator->getUser());
                $user->updateInfo($telegramAdministrator->getUser());
            }

            $administratorUsers[] = $user;

            if (!in_array($user, $currentAdministrators)) {
                $user->link('chats', $chat, ['status' => $telegramAdministrator->getStatus()]);
            }
        }


        foreach ($currentAdministrators as $currentAdministrator) {
            if (!in_array($currentAdministrator, $administratorUsers)) {
                $telegramChatMember = $this->getBotApi()->getChatMember(
                    $chat->chat_id,
                    $currentAdministrator->provider_user_id
                );

                if ($telegramChatMember->isActualChatMember()) {
                    $chatMember = ChatMember::findOne(['chat_id' => $chat->id, 'user_id' => $currentAdministrator->id]);
                    $chatMember->setAttributes([
                        'status' => $telegramChatMember->getStatus(),
                    ]);

                    $chatMember->save();
                } else {
                    $chat->unlink('users', $currentAdministrator, true);
                }
            }
        }

        $telegramChat = $this->getBotApi()->getChat($chat->chat_id);
        if (!$telegramChat) {
            $chat -> unlinkAll('phrases');
            $chat -> unlinkAll('settings');
            $chat -> unlinkAll('users');
            $chat -> delete();
        }

        if (!$currentUserIsAdmin || !$telegramChat) {
            $this->getState()->setName(self::createRoute('index'));
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
                ->editMessageTextOrSendMessage(
                    $this->render('refresh'),
                    [
                        [
                            [
                                'callback_data' => self::createRoute('index', [
                                    'chatId' => $chatId,
                                ]),
                                'text' => '🔙',
                            ],
                        ]
                    ]
                )
                ->build();
    }
}
