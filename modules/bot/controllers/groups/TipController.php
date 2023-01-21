<?php

namespace app\modules\bot\controllers\groups;

use app\models\WalletTransaction;
use app\modules\bot\components\Controller;
use app\modules\bot\controllers\privates\DeleteMessageController;
use app\modules\bot\controllers\privates\GroupTipController;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatTip;
use app\modules\bot\models\ChatTipWalletTransaction;
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
        // delete /tip message
        if ($this->getUpdate() && $this->getUpdate()->getMessage() && !$this->getUpdate()->getCallbackQuery()) {
            $this->getResponseBuilder()
                ->deleteMessage()
                ->send();
        }

        $fromUser = $this->getTelegramUser();
        $chat = $this->getTelegramChat();

        if ($replyMessage = $this->getMessage()->getReplyToMessage()) {
            $toUser = User::findOne([
                'provider_user_id' => $replyMessage->getFrom()->getId(),
                'is_bot' => 0,
            ]);

            // check if $fromUser is a chat member && $fromUser is not tipping himself
            $fromChatMember = $chat->getChatMemberByUser($fromUser);
            if (isset($toUser) && isset($fromChatMember) && ($toUser->getUserId()) != $fromUser->getUserId()) {
                $fromUser->sendMessage(
                    $this->render('/privates/tip', [
                        'chat' => $chat,
                        'toUser' => $toUser,
                    ]),
                    [
                        [
                            [
                                'callback_data' => GroupTipController::createRoute('view', [
                                    'chatId' => $chat->id,
                                    'toUserId' => $toUser->getUserId(),
                                    'replyMessageId' => $replyMessage->getMessageId()
                                ]),
                                'text' => Yii::t('bot', 'Tip'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => DeleteMessageController::createRoute(),
                                'text' => 'CANCEL',
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
     * @param int $replyMessageId Message->id
     *
     * @return array
     */
    public function actionShowTipMessage($chatId, $walletTransaction, $replyMessageId)
    {
        $toUser = $walletTransaction->toUser->botUser;
        $currency = $walletTransaction->currency;

        // send message
        $response = $this->getResponseBuilder()
            ->sendMessage(
                $this->render('show-tip-message', [
                    'toUser' => $toUser,
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
                    'replyToMessageId' => $replyMessageId,
                ]
            )
            ->send();

        if ($response) {
            // create new ChatTip record
            $newChatTip = new ChatTip([
                'chat_id' => $chatId,
                'message_id' => $response->getMessageId(),
            ]);

            $newChatTip->save();

            // create new ChatTipWalletTransaction record
            $newChatTipWalletTransaction = new ChatTipWalletTransaction([
                'chat_tip_id' => $newChatTip->id,
                'transaction_id' => $walletTransaction->id,
            ]);

            $newChatTipWalletTransaction->save();
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
    public function actionRetryTip($chatId, $toUserId)
    {
        $fromUser = $this->getTelegramUser();
        $chat = Chat::findOne($chatId);
        $toUser = User::findOne($toUserId);

        // check if $fromUser is a chat member && $fromUser is not tipping himself
        $fromChatMember = $chat->getChatMemberByUser($fromUser);
        if (isset($toUser) && isset($fromChatMember) && ($toUser->getUserId()) != $fromUser->getUserId()) {
            $fromUser->sendMessage(
                $this->render('/privates/tip', [
                    'chat' => $chat,
                    'toUser' => $toUser,
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
                    [
                        [
                            'callback_data' => DeleteMessageController::createRoute(),
                            'text' => 'CANCEL',
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
    public function actionUpdateTipMessage($chatId, $walletTransaction, $messageId)
    {
        $toUser = $walletTransaction->toUser->botUser;

        // find ChatTip record
        $chatTip = ChatTip::findOne(['message_id' => $messageId]);
        if (!isset($chatTip)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        // create new ChatTipWalletTransaction record
        $newChatTipWalletTransaction = new ChatTipWalletTransaction([
            'chat_tip_id' => $chatTip->id,
            'transaction_id' => $walletTransaction->id,
        ]);

        $newChatTipWalletTransaction->save();

        // find all transactions for tip message
        $transactions = WalletTransaction::find()
            ->joinWith('chatTipWalletTransaction')
            ->where([ChatTipWalletTransaction::tableName() . '.chat_tip_id' => $chatTip->id])
            ->all();

        // calculate amount according to currency
        $totalAmounts = [];
        foreach ($transactions as $transaction) {
            if (!array_key_exists($transaction->currency->code, $totalAmounts)) {
                $totalAmounts[$transaction->currency->code] = $transaction->amount;
            } else {
                $totalAmounts[$transaction->currency->code] += $transaction->amount;
            }
        }

        // edit message
        return $this->getResponseBuilder()
            ->editMessage(
                $messageId,
                $this->render('update-tip-message', [
                    'totalAmounts' => $totalAmounts,
                    'toUser' => $toUser,
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
    }
}
