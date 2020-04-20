<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\commands\EditMessageTextCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\components\response\commands\DeleteMessageCommand;

use app\modules\bot\components\response\ResponseBuilder;

/**
 * Class AdminVoteBanController
 *
 * @package app\controllers\bot
 */
class AdminVoteBanController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $statusSetting = $chat->getSetting(ChatSetting::VOTE_BAN_STATUS);

        if (!isset($statusSetting)) {
            $statusSetting = new ChatSetting();

            $statusSetting->setAttributes([
                'chat_id' => $chatId,
                'setting' => ChatSetting::VOTE_BAN_STATUS,
                'value' => ChatSetting::VOTE_BAN_STATUS_OFF,
            ]);

            $statusSetting->save();
        }

        $chatTitle = $chat->title;
        $statusOn = ($statusSetting->value == ChatSetting::VOTE_BAN_STATUS_ON);

        $voteLimitSetting = $chat->getSetting(ChatSetting::VOTE_BAN_LIMIT);
        $voteLimit =  isset($voteLimitSetting) ? $voteLimitSetting->value : ChatSetting::VOTE_BAN_LIMIT_DEFAULT;

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chatTitle')),
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
                            // TODO add limit feature
                            [
                                'callback_data' => self::createRoute('enter-limit', [
                                    'chatId' => $chatId,
                                ]),
                                'text' => Yii::t('bot', 'Limit') . ': ' . $voteLimit,
                            ],
                        ],
                        [
                            [
                                'callback_data' => AdminChatController::createRoute('index', [
                                    'chatId' => $chatId,
                                ]),
                                'text' => '🔙',
                            ],
                        ]

                ]
            )
            ->build();
    }

    public function actionUpdate($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $statusSetting = $chat->getSetting(ChatSetting::VOTE_BAN_STATUS);

        if ($statusSetting->value == ChatSetting::VOTE_BAN_STATUS_ON) {
            $statusSetting->value = ChatSetting::VOTE_BAN_STATUS_OFF;
        } else {
            $statusSetting->value = ChatSetting::VOTE_BAN_STATUS_ON;
        }

        $statusSetting->save();

        return $this->actionIndex($chatId);
    }

    public function actionEnterLimit($chatId = null)
    {
        $this->getState()->setName(self::createRoute('update-limit', [
            'chatId' => $chatId,
        ]));
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->sendMessage(
                $this->render('update-limit')
            )
            ->build();
    }

    public function actionUpdateLimit($chatId = null)
    {
        $update = $this->getUpdate();
        $message = $update->getMessage();
        $value =  (int) $message->getText();


        $chat = Chat::findOne($chatId);
        $statusSetting = $chat->getSetting(ChatSetting::VOTE_BAN_LIMIT);

        if (!(($value <= ChatSetting::VOTE_BAN_LIMIT_MAX) && ($value >= ChatSetting::VOTE_BAN_LIMIT_MIN))) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->deleteMessage($message)
                ->build();
        }

        if (!isset($statusSetting)) {
            $statusSetting = new ChatSetting();
            $statusSetting->setAttributes([
                'chat_id' => $chatId,
                'setting' => ChatSetting::VOTE_BAN_LIMIT,
            ]);
        }
        $statusSetting->value= (string) $value;
        $statusSetting->save();


        $this->getState()->setName(self::createRoute('index', [
            'chatId' => $chatId,
        ]));

        $this->module->dispatchRoute($update);
    }
}
