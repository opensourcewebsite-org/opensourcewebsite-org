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
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }
}
