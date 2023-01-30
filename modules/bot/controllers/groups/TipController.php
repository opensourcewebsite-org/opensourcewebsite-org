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
     * @param int|null $chatTipId ChatTip->id
     *
     * @return array
     */
    public function actionIndex($chatTipId = null)
    {
        // delete /tip message
        if ($this->getUpdate() && $this->getUpdate()->getMessage() && !$this->getUpdate()->getCallbackQuery()) {
            $this->getResponseBuilder()
                ->deleteMessage()
                ->send();
        }

        $fromUser = $this->getTelegramUser();

        if (!isset($chatTipId)) {
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

                if ($chatMember->isAnonymousAdministrator()) {
                    return $this->getResponseBuilder()
                        ->answerCallbackQuery()
                        ->build();
                }

                $chatTip = new ChatTip([
                    'chat_id' => $chat->id,
                    'to_user_id' => $toUser->getId(),
                    'reply_message_id' => $replyMessage->getMessageId(),
                ]);

                $chatTip->save();
            }
        } else {
            $chatTip = ChatTip::findOne($chatTipId);

            if (!isset($chatTip)) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery()
                    ->build();
            }

            $chat = $chatTip->chat;
            $toUser = $chatTip->toUser;
        }

        // check if $fromUser is a chat member && $fromUser is not tipping himself
        $fromChatMember = $chat->getChatMemberByUser($fromUser);
        if (isset($toUser) && isset($fromChatMember) && ($toUser->getId()) != $fromUser->getId()) {
            $fromUser->sendMessage(
                $this->render('/privates/tip', [
                    'chat' => $chat,
                    'toUser' => $toUser,
                ]),
                [
                    [
                        [
                            'callback_data' => SendGroupTipController::createRoute('index', [
                                'chatTipId' => $chatTip->id,
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
     * @param int $chatTipWalletTransactionId
     *
     * @return array
     */
    public function actionShowTipMessage($chatTipWalletTransactionId = null)
    {
        $chatTipWalletTransaction = ChatTipWalletTransaction::findOne(['id' => $chatTipWalletTransactionId]);
        $chatTip = $chatTipWalletTransaction->chatTip;
        $walletTransaction = $chatTipWalletTransaction->walletTransaction;
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
                                'chatTipId' => $chatTip->id,
                            ]),
                            'text' => Yii::t('bot', 'Tip'),
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                    'disableNotification' => true,
                    'replyToMessageId' => $chatTip->reply_message_id,
                ]
            )
            ->send();

        if ($response) {
            // add message_id to $chatTip record
            $chatTip->message_id = $response->getMessageId();
            $chatTip->sent_at = $response->getDate();
            $chatTip->save();
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param int $chatTipWalletTransactionId
     *
     * @return array
     */
    public function actionUpdateTipMessage($chatTipWalletTransactionId)
    {
        $chatTipWalletTransaction = ChatTipWalletTransaction::findOne(['id' => $chatTipWalletTransactionId]);
        $chatTip = $chatTipWalletTransaction->chatTip;
        $toUser = $chatTipWalletTransaction->walletTransaction->toUser->botUser;

        // find all transactions for tip message
        $walletTransactions = $chatTip->getWalletTransactions()->all();

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
                                'chatTipId' => $chatTip->id,
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
