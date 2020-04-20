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
use yii\helpers\ArrayHelper;

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
        }
        if (!isset($chat)) {
            return [];
        }

        $tmAdmins = $this->getBotApi()->getChatAdministrators($chat->chat_id);
        $tmAdminsIds = ArrayHelper::getColumn($tmAdmins, function ($el) {
            return $el->getUser()->getId();
        });

        $currentUserIsAdmin = false;
        $currentUser = $this->getTelegramUser();
        $curAdmins = $chat->getAdministrators()->all();
        $curAdminsIndexdByIds = ArrayHelper::index($curAdmins, function ($el) {
            return $el->provider_user_id;
        });
        $curAdminsIds = array_keys($curAdminsIndexdByIds);

        if (in_array($currentUser->provider_user_id, $curAdminsIds)) {
            $currentUserIsAdmin = true;
        }

        $administratorUsers = [];
        $users = ArrayHelper::index(User::find(['provider_user_id' => $tmAdminsIds])->all(), 'provider_user_id');

        foreach ($tmAdmins as $tmAdmin) {
            $user = isset($users[$tmAdmin->getUser()->getId()]) ? $users[$tmAdmin->getUser()->getId()] : null;
            if (!isset($user)) {
                $user = User::createUser($tmAdmin->getUser());
                $user->updateInfo($tmAdmin->getUser());
            }
            $administratorUsers[] = $user;
            if (!in_array($user->provider_user_id, $curAdminsIds)) {
                $user->link('chats', $chat, ['status' => $tmAdmin->getStatus()]);
            }
        }

        foreach ($curAdmins as $curAdmin) {
            if (!in_array($curAdmin->provider_user_id, $tmAdminsIds)) {
                $telegramChatMember = $this->getBotApi()->getChatMember(
                    $chat->chat_id,
                    $curAdmin->provider_user_id
                );

                if ($telegramChatMember->isActualChatMember()) {
                    $chatMember = ChatMember::findOne(['chat_id' => $chat->id, 'user_id' => $curAdmin->id]);
                    $chatMember->setAttributes([
                        'status' => $telegramChatMember->getStatus(),
                    ]);

                    $chatMember->save();
                } else {
                    $chat->unlink('users', $curAdmin, true);
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
