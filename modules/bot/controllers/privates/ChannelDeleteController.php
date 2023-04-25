<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\User;
use Yii;

/**
 * Class ChannelDeleteController
 *
 * @package app\modules\bot\controllers\privates
 */
class ChannelDeleteController extends Controller
{
    /**
     * @param int $id Chat->id
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionIndex($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isChannel()) {
            return $this->run('channel/index');
        }

        $user = $this->getTelegramUser();

        $chatMember = ChatMember::findOne([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
        ]);

        if (!isset($chatMember) || !$chatMember->isCreator()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->clearInputRoute();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'chat' => $chat,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('confirm', [
                                'id' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'YES'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => ChannelController::createRoute('view', [
                                'chatId' => $chat->id,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ]
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionConfirm($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isChannel()) {
            return $this->run('channel/index');
        }

        $user = $this->getTelegramUser();

        $chatMember = ChatMember::findOne([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
        ]);

        if (!isset($chatMember) || !$chatMember->isCreator()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chat->delete();

        return $this->run('channel/index');
    }
}
