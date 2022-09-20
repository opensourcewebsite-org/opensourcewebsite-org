<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use Yii;

/**
 * Class GroupBasicCommandsController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupBasicCommandsController extends Controller
{
    /**
    * @param int $id Chat->id
    * @return array
    */
    public function actionIndex($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chat')),
                [
                        [
                            [
                                'callback_data' => self::createRoute('set-status', [
                                    'id' => $chat->id,
                                ]),
                                'text' => $chat->basic_commands_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                            ],
                        ],
                        [
                            [
                                'callback_data' => GroupController::createRoute('view', [
                                    'chatId' => $chat->id,
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

    /**
    * @param int $id Chat->id
    * @return array
    */
    public function actionSetStatus($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        switch ($chat->basic_commands_status) {
            case ChatSetting::STATUS_ON:
                $chat->basic_commands_status = ChatSetting::STATUS_OFF;

                break;
            case ChatSetting::STATUS_OFF:
                $chat->basic_commands_status = ChatSetting::STATUS_ON;

                break;
        }

        return $this->actionIndex($chat->id);
    }
}
