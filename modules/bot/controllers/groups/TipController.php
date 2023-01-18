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

            // check if $fromUser is a chat member && $toUser is not a bot && $fromUser is not tipping himself
            $fromChatMember = $chat->getChatMemberByUser($fromUser);
            if (isset($toUser) && isset($fromChatMember) && !$toUser->isBot() && ($toUser->getUserId()) != $fromUser->getUserId()) {
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
                ]
            )
            ->send();

        if ($response) {
            // create new record
            $newTipTransaction = new ChatTipMessage([
                'chat_id' => $chatId,
                'transaction_id' => $walletTransaction->id,
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

        // check if $fromUser is a chat member && $fromUser is not tipping himself
        $fromChatMember = $chat->getChatMemberByUser($fromUser);
        if (isset($toUser) && isset($fromChatMember) && ($toUser->getUserId()) != $fromUser->getUserId()) {
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
        $toUser = $walletTransaction->toUser->botUser;

        // create new record
        $newTipMessage = new ChatTipMessage([
            'chat_id' => $chatId,
            'transaction_id' => $walletTransaction->id,
            'message_id' => $messageId,
        ]);

        $newTipMessage->save();

        // find all transactions for message
        $transactions = WalletTransaction::find()
            ->joinWith('chatTipMessage')
            ->where([ChatTipMessage::tableName() . '.message_id' => $messageId])
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
