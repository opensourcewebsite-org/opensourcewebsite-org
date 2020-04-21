<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\Controller;
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
            $update = $this->getUpdate();

            if ($update->getCallbackQuery()) {
                return ResponseBuilder::fromUpdate($update)->editMessageTextOrSendMessage(
                    $this->render('index', compact('chatTitle')),
                    [
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
                    ]
                )->build();
            }

            return ResponseBuilder::fromUpdate($update)->editMessageTextOrSendMessage(
                $this->render('index', compact('chatTitle')),
                [
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
                    ]
            )->build();
        }
    }

    /**
     * @return array
     */
    public function actionRefresh($chatId = null)
    {
        $result = [];

        if ($chatId) {
            $chat = Chat::findOne($chatId);
        }

        if (isset($chat)) {
            $telegramChat = $this->getBotApi()->getChat($chat->chat_id);
            if (!isset($telegramChat)) {
                $chat->unlinkAll('phrases');
                $chat->unlinkAll('settings');
                $chat->unlinkAll('users');
                $chat->delete();
                $this->getState()->setName(self::createRoute('index'));
                return [];
            }

            $telegramAdministrators = $this->getBotApi()->getChatAdministrators($chat->chat_id);
            $telegramAdministratorsIds = ArrayHelper::getColumn($telegramAdministrators, function ($telegramAdministrator) {
                return $telegramAdministrator->getUser()->getId();
            });

            $currentUser = $this->getTelegramUser();
            $currentUserIsAdministrator = false;
            if (in_array($currentUser->provider_user_id, $telegramAdministratorsIds)) {
                $currentUserIsAdministrator = true;
            }

            $curAdministrators = $chat->getAdministrators()->all();
            $curAdministratorsIndexdByIds = ArrayHelper::index($curAdministrators, function ($curAdministrator) {
                return $curAdministrator->provider_user_id;
            });
            $curAdministratorsIds = array_keys($curAdministratorsIndexdByIds);

            $outdatedAdministrators = $chat->getAdministrators()
                                ->andWhere(['not',['provider_user_id'=>$telegramAdministratorsIds]])
                                ->all();

            foreach ($outdatedAdministrators as $outdatedAdministrator) {
                $telegramChatMember = $this->getBotApi()->getChatMember(
                    $chat->chat_id,
                    $outdatedAdministrator->provider_user_id
                );
                if ($telegramChatMember->isActualChatMember()) {
                    $chatMember = ChatMember::findOne(['chat_id' => $chat->id, 'user_id' => $outdatedAdministrator->id]);
                    $chatMember->setAttributes([
                        'status' => $telegramChatMember->getStatus(),
                    ]);
                    $chatMember->save();
                    continue;
                }
                $chat->unlink('users', $outdatedAdministrator, true);
            }

            $users = ArrayHelper::index(User::find(['provider_user_id' => $telegramAdministratorsIds])->all(), 'provider_user_id');
            foreach ($telegramAdministrators as $telegramAdministrator) {
                $user = isset($users[$telegramAdministrator->getUser()->getId()]) ? $users[$telegramAdministrator->getUser()->getId()] : null;
                if (!isset($user)) {
                    $user = User::createUser($telegramAdministrator->getUser());
                    $user->updateInfo($telegramAdministrator->getUser());
                }
                if (!in_array($user->provider_user_id, $curAdministratorsIds)) {
                    $user->link('chats', $chat, ['status' => $telegramAdministrator->getStatus()]);
                }
            }

            if (!$currentUserIsAdministrator) {
                $this->getState()->setName(self::createRoute('index'));
            }

            $result = ResponseBuilder::fromUpdate($this->getUpdate())
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

        return $result;
    }
}
