<?php

namespace app\modules\bot\controllers\groups;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\models\ChatGreeting;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
use app\modules\bot\components\helpers\ExternalLink;

/**
 * Class GreetingController
 *
 * @package app\modules\bot\controllers\groups
 */
class GreetingController extends Controller
{
    /**
     * Action shows greeting message
     *
     * @return array
     */
    public function actionShowGreeting($telegramUserId = null)
    {
        $chat = $this->getTelegramChat();

        if ($chat->greeting_status == ChatSetting::STATUS_ON) {
            if (!empty($telegramUserId)) {
                $telegramUser = User::findOne($telegramUserId);
            } else {
                $telegramUser = $this->getTelegramUser();
            }

            $hasGreeting = ChatGreeting::find()
                ->where([
                    'chat_id' => $chat->id,
                    'provider_user_id' => $telegramUser->provider_user_id,
                ])
                ->exists();

            if (!$hasGreeting) {
                $response =  $this->getResponseBuilder()
                    ->sendMessage(
                        $this->render('show-greeting', [
                            'user' => $telegramUser,
                            'message' => $chat->greeting_message,
                        ]),
                        [
                            [
                                [
                                    'url' => ExternalLink::getBotGroupGuestLink($chat->getChatId()),
                                    'text' => Yii::t('bot', 'FAQ'),
                                    'visible' => $chat->faq_status == ChatSetting::STATUS_ON,
                                ],
                            ],
                        ],
                        [
                            'disablePreview' => true,
                            'disableNotification' => true,
                        ]
                    )
                    ->send();

                if ($response) {
                    $greeting = new ChatGreeting([
                        'chat_id' => $chat->id,
                        'provider_user_id' => $telegramUser->provider_user_id,
                        'message_id' => $response->getMessageId(),
                    ]);

                    $greeting->save();

                    // remove other active greetings
                    $this->deletePreviousGreetings($greeting);
                }
            }
        }
    }

    private function deletePreviousGreetings(ChatGreeting $currentGreeting)
    {
        $prevGreetings = ChatGreeting::find()
            ->where([
                'chat_id' => $currentGreeting->chat_id,
            ])
            ->andWhere(['!=', 'id', $currentGreeting->id])
            ->all();

        /** @var ChatGreeting $message */
        foreach ($prevGreetings as $message) {
            $this->getBotApi()->deleteMessage($message->chat->chat_id, $message->message_id);

            $message->delete();
        }
    }
}
