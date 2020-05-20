<?php

namespace app\modules\bot\controllers\privates;

use Yii;

use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\User;
use yii\helpers\ArrayHelper;
use TelegramBot\Api\HttpException;

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
                return $this->getResponseBuilder()
                    ->editMessageTextOrSendMessage(
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
                                    'text' => Yii::t('bot', 'Star Top'),
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
                                    'callback_data' => AdminChatRefreshController::createRoute('index', [
                                        'chatId' => $chatId,
                                    ]),
                                    'text' => '🔄',
                                ],
                            ],
                        ])
                        ->build();
            }

            return $this->getResponseBuilder()
                ->editMessageTextOrSendMessage(
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
}
