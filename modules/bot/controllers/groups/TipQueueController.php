<?php

namespace app\modules\bot\controllers\groups;

use app\helpers\Number;
use app\models\traits\FloatAttributeTrait;
use app\models\WalletTransaction;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatTip;
use app\modules\bot\models\ChatTipQueue;
use app\modules\bot\models\ChatTipQueueUser;
use app\modules\bot\models\User;
use Yii;

/**
 * Class TipQueueController
 *
 * @package app\modules\bot\controllers\groups
 */
class TipQueueController extends Controller
{
    use FloatAttributeTrait;
    /**
     * @param int $queueId ChatTipQueue->id
     *
     * @return array
     */
    public function actionTipMessage($queueId = null)
    {
        $chatTipQueue = ChatTipQueue::findOne($queueId);

        if (!isset($chatTipQueue)) {
            return [];
        }

        $buttonVisible = true;

        if ($chatTipQueue->state == ChatTipQueue::CLOSED_STATE) {
            $buttonVisible = false;
        }

        $queueUsers = $chatTipQueue->getQueueUsers();
        $totalUserCount = $queueUsers->count();

        if ($totalUserCount >= $chatTipQueue->userCount) {
            $buttonVisible = false;
        }

        if ($chatTipQueue->message_id) {
            // edit message
            return $this->getResponseBuilder()
                ->editMessage(
                    $chatTipQueue->message_id,
                    $this->render('tip-message', [
                        'chatTipQueue' => $chatTipQueue,
                    ]),
                    [
                        [
                            [
                                'callback_data' => self::createRoute('take-tip', [
                                    'queueId' => $chatTipQueue->id,
                                ]),
                                'text' => Emoji::GIFT,
                                'visible' => $buttonVisible,
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

        // send message
        $response = $this->getResponseBuilder()
            ->sendMessage(
                $this->render('tip-message', [
                    'chatTipQueue' => $chatTipQueue,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('take-tip', [
                                'queueId' => $chatTipQueue->id,
                            ]),
                            'text' => Emoji::GIFT,
                            'visible' => $chatTipQueue->state == ChatTipQueue::OPEN_STATE,
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
            // add message_id to $chatTipQueue record
            $chatTipQueue->message_id = $response->getMessageId();
            $chatTipQueue->save();

            return $response;
        }

        return [];
    }

    public function actionTakeTip($queueId = null)
    {
        $chatTipQueue = ChatTipQueue::findOne($queueId);

        if (!$chatTipQueue || $chatTipQueue->state != ChatTipQueue::OPEN_STATE) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $botUser = $this->getTelegramUser();

        if (false && $botUser->id == $chatTipQueue->user->id) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $query = $chatTipQueue->getQueueUsers();
        $userCount = $query->count();

        if ($userCount > $chatTipQueue->userCount) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $queueUser = ChatTipQueueUser::findOne([
            'queue_id' => $queueId,
            'user_id' => $botUser->id,
        ]);

        if ($queueUser) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $queueUser = new ChatTipQueueUser([
            'queue_id' => $queueId,
            'user_id' => $botUser->id,
        ]);

        $queueUser->save();

        $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();

        $this->processAllQueues();
    }

    public function processAllQueues($ignoreUserAbort = true)
    {
        if (!Yii::$app->mutex->acquire(ChatTipQueue::MUTEX_KEY)) {
            return false;
        }

        if ($ignoreUserAbort) {
            ignore_user_abort(true);
            set_time_limit(0);
        }

        function updateMessage($queue)
        {
            $module = Yii::$app->getModule('bot');
            $chat = $module->getChat();
            $module->setChat($queue->chat);
            $module->runAction('tip-queue/tip-message', [
                'queueId' => $queue->id,
            ]);
            $module->setChat($chat);
        }

        foreach (ChatTipQueueUser::getActiveUsers()->each(1) as $user) {
            $queue = $user->queue;

            if ($queue->state != ChatTipQueue::OPEN_STATE) {
                continue;
            }

            $queueUsers = $queue->getQueueUsers();
            $totalUserCount = $queueUsers->count();

            if ($totalUserCount >= $queue->userCount) {
                $processedUserCount = $queueUsers->where(['>', 'transaction_id', '0'])->count();
                if ($processedUserCount >= $queue->userCount) {
                    goto CLOSE_QUEUE;
                }

                updateMessage($queue);
            }

            $walletTransaction = new WalletTransaction([
                'from_user_id' => $queue->user->globalUser->id,
                'to_user_id' => $user->user->globalUser->id,
                'amount' => $queue->userAmount,
                'currency_id' => $queue->currency->id,
                'type' => WalletTransaction::TIP_WITHOUT_REPLY_TYPE,
            ]);

            $walletTransactionId = $queue->user->globalUser->createTransaction($walletTransaction);

            if (!$walletTransactionId) {
                $wallet = $queue->user->globalUser->getWalletByCurrencyId($queue->getCurrencyId());
                if (!$wallet->hasAmount($queue->userAmount)) {
                    goto CLOSE_QUEUE;
                }

                $user->delete();
                updateMessage($queue);
                continue;
            }

            $user->transaction_id = $walletTransactionId;
            $user->save();

            $walletTransaction->toUser->botUser->sendMessage(
                $this->render('receiver-privates-success', [
                    'walletTransaction' => $walletTransaction,
                    'queue' => $queue,
                    'toUserWallet' => $walletTransaction->toUser->botUser->getWalletByCurrencyId($walletTransaction->currency->id),
                ]),
                []
            );

            $processedUserCount = $queueUsers->where(['>', 'transaction_id', '0'])->count();

            if ($processedUserCount >= $queue->userCount) {
                goto CLOSE_QUEUE;
            }

            $wallet = $queue->user->globalUser->getWalletByCurrencyId($queue->getCurrencyId());
            if (!$wallet->hasAmount($queue->userAmount)) {
                goto CLOSE_QUEUE;
            }

            updateMessage($queue);

            continue;

            CLOSE_QUEUE:

            $queue->close();
            updateMessage($queue);

            if ($queueUsers->where(['>', 'transaction_id', '0'])->count() < 1) {
                $this->getBotApi()->deleteMessage($queue->chat->getChatId(), $queue->getMessageId());
            }
        }

        Yii::$app->mutex->release(ChatTipQueue::MUTEX_KEY);

        sleep(3);

        $user = ChatTipQueueUser::getActiveUsers()->one();

        if ($user) {
            return self::processAllQueues();
        }
    }
}
