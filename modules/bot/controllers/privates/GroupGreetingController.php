<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageWithEntitiesConverter;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use Yii;

/**
 * Class GroupGreetingController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupGreetingController extends Controller
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

        $this->getState()->setName(null);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chat', 'telegramUser')),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'chatId' => $chatId,
                            ]),
                            'text' => $chat->greeting_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-message', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Message'),
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
                    ],
                ],
                [
                    'disablePreview' => true,
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

        switch ($chat->greeting_status) {
            case ChatSetting::STATUS_ON:
                $chat->greeting_status = ChatSetting::STATUS_OFF;

                break;
            case ChatSetting::STATUS_OFF:
                $chatMember = $chat->getChatMemberByUserId();

                 if (!$chatMember->trySetChatSetting('greeting_status', ChatSetting::STATUS_ON)) {
                     return $this->getResponseBuilder()
                         ->answerCallbackQuery(
                             $this->render('alert-status-on', [
                                 'requiredRating' => $chatMember->getRequiredRatingForChatSetting('greeting_status', ChatSetting::STATUS_ON),
                             ]),
                             true
                         )
                         ->build();
                 }

                break;
        }

        return $this->actionIndex($chatId);
    }

    public function actionSetMessage($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $this->getState()->setName(self::createRoute('set-message', [
                'chatId' => $chatId,
            ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = MessageWithEntitiesConverter::toHtml($this->getUpdate()->getMessage())) {
                if ($chat->validateSettingValue('greeting_message', $text)) {
                    $chat->greeting_message = $text;

                    return $this->runAction('index', [
                         'chatId' => $chatId,
                     ]);
                }
            }
        }

        $messageMarkdown = MessageWithEntitiesConverter::fromHtml($chat->greeting_message ?? '');

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-message', [
                    'messageMarkdown' => $messageMarkdown,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }
}
