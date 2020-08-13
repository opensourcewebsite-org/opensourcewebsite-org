<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\User;
use yii\helpers\ArrayHelper;

/**
 * Class AdminChatController
 *
 * @package app\modules\bot\controllers\privates
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

            // TODO refactoring, для того чтобы ограничить доступ к настройкам группы
            if ($this->getUpdate()->getCallbackQuery()) {
                $admins = $chat->getAdministrators()->all();

                return $this->getResponseBuilder()
                    ->editMessageTextOrSendMessage(
                        $this->render('index', compact('chatTitle', 'admins')),
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
                                    'callback_data' => AdminJoinCaptchaController::createRoute('index', [
                                        'chatId' => $chatId,
                                    ]),
                                    'text' => Yii::t('bot', 'Join Captcha'),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => AdminGreetingController::createRoute('index', [
                                        'chatId' => $chatId,
                                    ]),
                                    'text' => Yii::t('bot', 'Greeting'),
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
                                    'callback_data' => AdminStarTopController::createRoute('index', [
                                        'chatId' => $chatId,
                                    ]),
                                    'text' => Yii::t('bot', 'Karma'),
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
                                    'callback_data' => AdminController::createRoute(),
                                    'text' => Emoji::BACK,
                                ],
                                [
                                    'callback_data' => MenuController::createRoute(),
                                    'text' => Emoji::MENU,
                                ],
                                [
                                    'callback_data' => AdminChatRefreshController::createRoute('index', [
                                        'chatId' => $chatId,
                                    ]),
                                    'text' => Emoji::REFRESH,
                                ],
                            ],
                        ]
                    )
                    ->build();
            }

            return [];
        }
    }
}
