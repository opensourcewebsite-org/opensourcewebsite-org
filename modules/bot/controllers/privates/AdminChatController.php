<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;
use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;

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
        $chat = Chat::findOne($chatId);
        if (!isset($chat)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        $chatTitle = $chat->title;

        return ResponseBuilder::fromUpdate($this->getUpdate())
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
                            'text' => '🏗 ' . Yii::t('bot', 'Vote Ban'),
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
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => self::createRoute('refresh', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Emoji::REFRESH,
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionRefresh($chatId = null)
    {
        // TODO add refresh for selected group
        if ($chatId) {
            $chat = Chat::findOne($chatId);

            if (isset($chat)) {

            }
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->build();
    }
}
