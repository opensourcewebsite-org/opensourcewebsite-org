<?php

namespace app\modules\bot\controllers\groups;

use app\models\WalletTransaction;
use app\modules\bot\components\Controller;
use app\modules\bot\controllers\privates\GroupTipController;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatTipMessage;
use app\modules\bot\models\User;
use Yii;

/**
 * Class TipController
 *
 * @package app\modules\bot\controllers\groups
 */
class TipController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $fromUser = $this->getTelegramUser();
        $chat = $this->getTelegramChat();

        if ($replyMessage = $this->getMessage()->getReplyToMessage()) {
            $toUser = User::findOne([
                'provider_user_id' => $replyMessage->getFrom()->getId(),
            ]);

            // check if $fromUser is a chat member and $toUser is not a bot
            $fromChatMember = $chat->getChatMemberByUser($fromUser);
            if (isset($toUser) && isset($fromChatMember) && !$toUser->isBot()) {
                $fromUser->sendMessage(
                    $this->render('/privates/tip', [
                        'chat' => $chat,
                    ]),
                    [
                        [
                            [
                                'callback_data' => GroupTipController::createRoute('view', [
                                    'chatId' => $chat->id,
                                    'toUserId' => $toUser->getUserId(),
                                ]),
                                'text' => Yii::t('bot', 'Tip'),
                            ],
                        ],
                    ]
                );
            }
        }

        return [];
    }

    /**
     * @param int $chatId Chat->id
     * @param WalletTransaction $walletTransaction
     *
     * @return array
     */
    public function actionShowTipMessage($chatId, $walletTransaction) {
        $fromUser = $walletTransaction->fromUser->botUser;
        $toUser = $walletTransaction->toUser->botUser;
        $currency = $walletTransaction->currency;

        // send message
        $response = $this->getResponseBuilder()
            ->sendMessage(
                $this->render('show-tip-message', [
                    'fromUsername' => $fromUser->getUsername(),
                    'toUsername' => $toUser->getUsername(),
                    'amount' => $walletTransaction->getAmount(),
                    'code' => $currency->code,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('retry-tip', [
                                'chatId' => $chatId,
                                'toUserId' => $walletTransaction->getToUserId(),
                            ]),
                            'text' => Yii::t('bot', 'Tip'),
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
            // create new record
            $newTipTransaction = new ChatTipMessage([
                'chat_id' => $chatId,
                'from_user_id' => $walletTransaction->from_user_id,
                'to_user_id' => $walletTransaction->to_user_id,
                'currency_id' => $walletTransaction->currency_id,
                'amount' => $walletTransaction->amount,
                'message_id' => $response->getMessageId(),
            ]);

            $newTipTransaction->save();
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param int $chatId Chat->id
     * @param int $toUserId User->id
     *
     * @return array
     */
    public function actionRetryTip($chatId, $toUserId) {
        $fromUser = $this->getTelegramUser();
        $chat = Chat::findOne($chatId);
        $toUser = User::findOne($toUserId);

        // check if sender is a chat member
        $fromChatMember = $chat->getChatMemberByUser($fromUser);
        if (isset($toUser) && isset($fromChatMember)) {
            $fromUser->sendMessage(
                $this->render('/privates/tip', [
                    'chat' => $chat,
                ]),
                [
                    [
                        [
                            'callback_data' => GroupTipController::createRoute('view', [
                                'chatId' => $chatId,
                                'toUserId' => $toUserId,
                                'messageId' => $this->getUpdate()->getRequestMessage()->getMessageId(),
                            ]),
                            'text' => Yii::t('bot', 'Tip'),
                        ],
                    ],
                ]
            );
        }

        return [];
    }

    /**
     * @param int $chatId Chat->id
     * @param WalletTransaction $walletTransaction
     * @param int $messageId Message->id
     *
     * @return array
     */
    public function actionUpdateTipMessage($chatId, $walletTransaction, $messageId) {
        $fromUser = $walletTransaction->fromUser->botUser;
        $toUser = $walletTransaction->toUser->botUser;
        $currency = $walletTransaction->currency;

        $existingTipMessage = ChatTipMessage::findOne([
            'message_id' => $messageId,
            'from_user_id' => $fromUser->getUserId(),
            'currency_id' => $walletTransaction->currency_id,
        ]);

        // check if user has already tipped with chosen currency
        if (!isset($existingTipMessage)) {
            // create new record
            $newTipMessage = new ChatTipMessage([
                'chat_id' => $chatId,
                'from_user_id' => $walletTransaction->from_user_id,
                'to_user_id' => $walletTransaction->to_user_id,
                'currency_id' => $walletTransaction->currency_id,
                'amount' => $walletTransaction->amount,
                'message_id' => $messageId,
            ]);

            $newTipMessage->save();
        } else {
            // increase amount
            $existingTipMessage->amount += $walletTransaction->amount;
            $existingTipMessage->save();
        }

        $tipMessages = ChatTipMessage::find()
            ->where(['message_id' => $messageId])
            ->all();

        // edit message
        $this->getResponseBuilder()
            ->editMessage(
                $messageId,
                $this->render('update-tip-message', [
                    'tipTransactions' => $tipMessages,
                    'toUser' => $toUser,
                    'code' => $currency->code,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('retry-tip', [
                                'chatId' => $chatId,
                                'toUserId' => $walletTransaction->getToUserId(),
                            ]),
                            'text' => Yii::t('bot', 'Tip'),
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                    'disableNotification' => true,
                ]
            )
            ->build();

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }
}
