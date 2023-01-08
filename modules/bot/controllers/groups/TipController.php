<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use app\modules\bot\controllers\privates\GroupTipController;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
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

            if ($toUser) {
                $toChatMember = ChatMember::findOne([
                    'chat_id' => $chat->id,
                    'user_id' => $toUser->id,
                ]);
            }

            if (isset($toChatMember)) {
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

    public function actionShowTipMessage($chatId, $walletTransaction) {
        $fromUser = User::findOne($walletTransaction->getFromUserId());
        $toUser = User::findOne($walletTransaction->getToUserId());
        $currency = $walletTransaction->getCurrency()->one();

        if ($fromUser && $toUser && $currency) {
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

        return [];
    }

    public function actionRetryTip($chatId, $toUserId) {
        $fromUser = $this->getTelegramUser();
        $chat = Chat::findOne($chatId);
        $toUser = User::findOne($toUserId);

        if ($toUser) {
            $toChatMember = ChatMember::findOne([
                'chat_id' => $chatId,
                'user_id' => $toUserId,
            ]);
        }

        if (isset($toChatMember)) {
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
