<?php

namespace  app\modules\bot\controllers\publics;

use app\modules\bot\components\Controller;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
use Yii;

/**
 *
 */
class RatingController extends Controller
{
    public function beforeAction($action)
    {
        $chat = $this->getTelegramChat();
        $chatId = $chat->chat_id;

        $isBotAdmin = false;
        $botUser = User::find()->where(['provider_user_name' => $this->getBotName()])->one();
        if ($botUser) {
            $isBotAdmin = ChatMember::find()->where(['chat_id' => $chat->id, 'user_id' => $botUser->id, 'status' => ChatMember::STATUS_ADMINISTRATOR])->exists();
        }

        $starTopStatus = $chat->getSetting(ChatSetting::STAR_TOP_STATUS)->value;
        $isStarTopOff = ($starTopStatus != ChatSetting::STAR_TOP_STATUS_ON);

        if (!$isBotAdmin || !parent::beforeAction($action) || $isStarTopOff) {
            return false;
        }
        return true;
    }

    public function actionIndex()
    {
        $update = $this->getUpdate();
        $message = $update->getMessage();
        $messageId = $message->getMessageId();
        $chat = $this->getTelegramChat();
        $chatId = $chat->chat_id;

        $response = ResponseBuilder::fromUpdate($this->getUpdate());
        $response->deleteMessage()
                ->sendMessage(
                    $this->render('index'),
                    [
                            [
                                [
                                    'callback_data' => self::createRoute('like-message', ['messageId' => $messageId]),
                                    'text' => '👍 ' . '' . Yii::t('bot', 'Like'),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => self::createRoute('dislike-message', ['messageId' => $messageId]),
                                    'text' => '👎 ' . '' . Yii::t('bot', 'Disike'),
                                ],
                            ]
                        ]
                );
        $message = $command->send($this->getBotApi());

        return $response->build();
    }


    public function actionLikeMessage($messageId)
    {
    }

    public function actionDislikeMessage($messageId)
    {
    }
}
