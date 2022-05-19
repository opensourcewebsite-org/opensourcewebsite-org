<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class GroupJoinCaptchaController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupJoinCaptchaController extends Controller
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

        $telegramUser = $this->getTelegramUser();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chat', 'telegramUser')),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'chatId' => $chatId,
                            ]),
                            'text' => $chat->join_captcha_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
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

        if ($chat->join_captcha_status == ChatSetting::STATUS_ON) {
            $chat->join_captcha_status = ChatSetting::STATUS_OFF;
        } else {
            $chat->join_captcha_status = ChatSetting::STATUS_ON;
        }

        return $this->actionIndex($chatId);
    }
}
