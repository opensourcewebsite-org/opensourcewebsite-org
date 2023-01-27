<?php

namespace app\modules\bot\controllers\groups;

use app\models\WalletTransaction;
use app\modules\bot\components\Controller;
use app\modules\bot\controllers\privates\DeleteMessageController;
use app\modules\bot\controllers\privates\SendGroupTipController;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
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
     * @param int|null $chatId Chat->id
     * @param int|null $toUserId User->id
     *
     * @return array
     */
    public function actionIndex($chatId = null, $toUserId = null)
    {
        // delete /tip message
        if ($this->getUpdate() && $this->getUpdate()->getMessage() && !$this->getUpdate()->getCallbackQuery()) {
            $this->getResponseBuilder()
                ->deleteMessage()
                ->send();
        }

        $fromUser = $this->getTelegramUser();
        $toUser = null;
        $replyMessageId = null;
        $messageId = null;

        if (!isset($chatId) && !isset($toUserId)) {
            $chat = $this->getTelegramChat();
            if ($replyMessage = $this->getMessage()->getReplyToMessage()) {
                $toUser = User::findOne([
                    'provider_user_id' => $replyMessage->getFrom()->getId(),
                    'is_bot' => 0,
                ]);

                $chatMember = ChatMember::findOne([
                    'chat_id' => $chat->id,
                    'user_id' => $toUser->id,
                ]);

                if (!$chatMember->isAnonymousAdministrator()) {
                    $replyMessageId = $replyMessage->getMessageId();
                }

            }
        } else {
            $chat = Chat::findOne($chatId);
            $toUser = User::findOne($toUserId);
            $messageId = $this->getUpdate()->getRequestMessage()->getMessageId();
        }

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
                            'callback_data' => SendGroupTipController::createRoute('index', [
                                'chatId' => $chat->id,
                                'toUserId' => $toUser->getUserId(),
                                'replyMessageId' => $replyMessageId,
                                'messageId' => $messageId,
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
     * @param ChatTipWalletTransaction $newChatTipWalletTransaction
     * @param int $replyMessageId Message->id
     *
     * @return array
     */
    public function actionShowTipMessage($newChatTipWalletTransaction = null, $replyMessageId = null)
    {
        $walletTransaction = $newChatTipWalletTransaction->walletTransaction;
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
                            'callback_data' => self::createRoute('index', [
                                'chatId' => $newChatTipWalletTransaction->chatTip->chat_id,
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
            // add message_id to $chatTip record
            $chatTip = $newChatTipWalletTransaction->chatTip;
            $chatTip->message_id = $response->getMessageId();
            $chatTip->sent_at = $response->getDate();
            $chatTip->save();
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param int $newChatTipWalletTransactionId Chat->id
     *
     * @return array
     */
    public function actionUpdateTipMessage($newChatTipWalletTransactionId)
    {
        $chatTipWalletTransaction = ChatTipWalletTransaction::findOne(['id' => $newChatTipWalletTransactionId]);
        $chatTip = $chatTipWalletTransaction->chatTip;
        $toUser = $chatTipWalletTransaction->walletTransaction->toUser->botUser;

        // find all transactions for tip message
        $walletTransactions = $chatTipWalletTransaction->getWalletTransactionsByChatTipId($chatTip->id);

        // calculate amount according to currency
        $totalAmounts = [];
        foreach ($walletTransactions as $transaction) {
            if (!array_key_exists($transaction->currency->code, $totalAmounts)) {
                $totalAmounts[$transaction->currency->code] = $transaction->amount;
            } else {
                $totalAmounts[$transaction->currency->code] += $transaction->amount;
            }
        }

        // edit message
        return $this->getResponseBuilder()
            ->editMessage(
                $chatTip->message_id,
                $this->render('update-tip-message', [
                    'totalAmounts' => $totalAmounts,
                    'toUser' => $toUser,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'chatId' => $chatTip->chat_id,
                                'toUserId' => $chatTipWalletTransaction->walletTransaction->getToUserId(),
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
