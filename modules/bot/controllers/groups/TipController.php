<?php

namespace app\modules\bot\controllers\groups;

use app\models\WalletTransaction;
use app\modules\bot\components\Controller;
use app\modules\bot\controllers\privates\GroupTipController;
use app\modules\bot\models\Chat;
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

        return $this->getResponseBuilder()
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
                            ]),
                            'text' => Yii::t('bot', 'Tip'),
                        ],
                    ],
                ]
            );
        }

        return [];
    }
}
