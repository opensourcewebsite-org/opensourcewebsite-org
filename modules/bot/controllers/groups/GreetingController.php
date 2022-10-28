<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\models\ChatGreeting;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
use Yii;

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
     * @param string|null $message
     * @param int|string|null $id User->provider_user_id|User->provider_user_name
     * @return array
     */
    public function actionIndex($message = null, $id = null)
    {
        $chat = $this->getTelegramChat();

        if ($chat->isGreetingOn()) {
            if (!$id) {
                if ($message) {
                    if ((int)$message[0] > 0) {
                        if (preg_match('/(?:^(?:[0-9]+))/i', $message, $matches)) {
                            $id = $matches[0];
                        }
                    } else {
                        if ($message[0] == '@') {
                            if (preg_match('/(?:^@(?:[A-Za-z0-9][_]{0,1})*[A-Za-z0-9]+)/i', $message, $matches)) {
                                $id = ltrim($matches[0], '@');
                            }
                        } else {
                            if (preg_match('/(?:(?:[A-Za-z0-9][_]{0,1})*[A-Za-z0-9]+)/i', $message, $matches)) {
                                $id = $matches[0];
                            }
                        }
                    }
                } elseif ($this->getMessage() && ($replyMessage = $this->getMessage()->getReplyToMessage())) {
                    $id = $replyMessage->getFrom()->getId();
                }
            }

            if ($id) {
                $viewUser = User::find()
                    ->andWhere([
                        'or',
                        ['provider_user_name' => $id],
                        ['provider_user_id' => $id],
                    ])
                    ->human()
                    ->one();
            } else {
                $viewUser = $this->getTelegramUser();
            }

            if (!$viewUser) {
                return [];
            }

            $hasGreeting = ChatGreeting::find()
                ->where([
                    'chat_id' => $chat->id,
                    'provider_user_id' => $viewUser->provider_user_id,
                ])
                ->exists();

            if (!$hasGreeting) {
                $response =  $this->getResponseBuilder()
                    ->sendMessage(
                        $this->render('show-greeting', [
                            'user' => $viewUser,
                            'message' => $chat->greeting_message,
                        ]),
                        [],
                        [
                            'disablePreview' => true,
                            'disableNotification' => true,
                        ]
                    )
                    ->send();

                if ($response) {
                    $greeting = new ChatGreeting([
                        'chat_id' => $chat->id,
                        'provider_user_id' => $viewUser->provider_user_id,
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
