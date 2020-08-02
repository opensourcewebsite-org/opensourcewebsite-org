<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\models\BotChatGreeting;
use app\modules\bot\models\BotChatGreetingMessage;
use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class AdminGreetingController
 *
 * @package app\controllers\bot
 */
class AdminGreetingController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($chatId = null)
    {
        $chat = Chat::findOne($chatId);
        $telegramUser = $this->getTelegramUser();

        if (!isset($chat)) {
            return [];
        }

        $statusSetting = $chat->getSetting(ChatSetting::GREETING_STATUS);

        if (!isset($statusSetting)) {
            $statusSetting = new ChatSetting();

            $statusSetting->setAttributes([
                'chat_id' => $chatId,
                'setting' => ChatSetting::GREETING_STATUS,
                'value' => ChatSetting::GREETING_STATUS_OFF,
            ]);

            $statusSetting->save();
        }

        $chatTitle = $chat->title;
        $statusOn = ($statusSetting->value == ChatSetting::GREETING_STATUS_ON);
        $chatGreetingMessage = $chat->getGreetingMessage()->one();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chatTitle', 'telegramUser', 'chatGreetingMessage')),
                [
                        [
                            [
                                'callback_data' => self::createRoute('update', [
                                    'chatId' => $chatId,
                                ]),
                                'text' => Yii::t('bot', 'Status') . ': ' . Yii::t('bot', ($statusOn ? 'ON' : 'OFF')),
                            ],
                        ],
                    [
                        [
                            'callback_data' => self::createRoute('message', [
                                //'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Message'),
                        ],
                    ],

                    [
                            [
                                'callback_data' => AdminChatController::createRoute('index', [
                                    'chatId' => $chatId,
                                ]),
                                'text' => Emoji::BACK,
                            ],
                        ]
                    ],
            )
            ->build();
    }

    public function actionUpdate($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $statusSetting = $chat->getSetting(ChatSetting::GREETING_STATUS);

        if ($statusSetting->value == ChatSetting::GREETING_STATUS_ON) {
            $statusSetting->value = ChatSetting::GREETING_STATUS_OFF;
        } else {
            $statusSetting->value = ChatSetting::GREETING_STATUS_ON;
        }

        $statusSetting->save();

        return $this->actionIndex($chatId);
    }

    public function actionMessage()
    {
        $this->getState()->setName(self::createRoute('save'));
        $chat = $this->getTelegramChat();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('enter-message'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'chatId' => $chat->id,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionSave()
    {
        $text = $this->getUpdate()->getMessage()->getText();
        $user = $this->getTelegramUser();
        $chat = $this->getTelegramChat();

        $botChatGreetingMessage = BotChatGreetingMessage::findOne([
            'chat_id' => $chat->id,
        ]);

        if (!isset($botChatGreetingMessage)) {
            $botChatGreetingMessage = new BotChatGreetingMessage();
        }

        $botChatGreetingMessage->setAttributes([
          'chat_id' => $chat->id,
          'updated_by' => $user->id,
          'value' => self::prepareText($text),
        ]);

        if ($botChatGreetingMessage->save()) {
            $botChatGreeting = BotChatGreeting::findOne([
                'chat_id' => $chat->id
            ]);
            if (!isset($botChatGreeting)) {
                $botChatGreeting = new BotChatGreeting();
            }
            $botChatGreeting->setAttributes([
                'chat_id' =>  $chat->id,
                'provider_user_id' => $user->provider_user_id,
                'message_id' => $botChatGreetingMessage->id,
            ]);
            $botChatGreeting->save();
        }

        return $this->actionIndex($chat->id);
    }

    private static function prepareText(string $text)
    {
        return nl2br(strip_tags($text, '<b><i>'));
    }
}
