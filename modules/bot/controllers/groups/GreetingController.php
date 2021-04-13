<?php

namespace app\modules\bot\controllers\groups;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\models\BotChatGreeting;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;

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
        $greetingStatus = $chat->getSetting(ChatSetting::GREETING_STATUS);

        if (isset($greetingStatus) && $greetingStatus->value == ChatSetting::GREETING_STATUS_ON) {
            if (!empty($telegramUserId)) {
                $telegramUser = User::findOne($telegramUserId);
            } else {
                $telegramUser = $this->getTelegramUser();
            }

            $messageSetting = $chat->getSetting(ChatSetting::GREETING_MESSAGE);

            $response =  $this->getResponseBuilder()
                ->sendMessage(
                    $this->render('show-greeting', [
                        'user' => $telegramUser,
                        'message' => $messageSetting->value,
                    ]),
                    [],
                    [
                        'disablePreview' => true,
                        'disableNotification' => true,
                    ]
                )
                ->send();

            if ($response) {
                $botGreeting = BotChatGreeting::find()
                    ->where([
                        'chat_id' => $chat->id,
                        'provider_user_id' => $telegramUser->provider_user_id,
                    ])
                    ->exists();

                if (!$botGreeting) {
                    $botGreeting = new BotChatGreeting([
                        'chat_id' => $chat->id,
                        'provider_user_id' => $telegramUser->provider_user_id,
                        'message_id' => $response->getMessageId(),
                    ]);
                    $botGreeting->save();
                }
                // remove all active previous greetings
                //$this->deletePreviousGreetings($botGreeting);
            }
        }
    }

    private function deletePreviousGreetings(BotChatGreeting $currentGreeting)
    {
        $prevGreetings = BotChatGreeting::find()
            ->where([
                'chat_id' => $currentGreeting->chat_id,
            ])
            ->andWhere(['!=', 'id', $currentGreeting->id])
            ->all();

        /** @var BotChatGreeting $message */
        foreach ($prevGreetings as $message) {
            try {
                $this->getBotApi()->deleteMessage($message->chat_id, $message->message_id);
            } catch (\Exception $e) {
                echo 'ERROR: BotChatGreeting #' . $message->id . ' (deleteMessage): ' . $e->getMessage() . "\n";
            }

            $message->delete();
        }
    }
}
