<?php

namespace app\modules\bot\controllers\groups;

use app\helpers\Number;
use app\models\WalletTransaction;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\controllers\privates\DeleteMessageController;
use app\modules\bot\controllers\privates\MemberController;
use app\modules\bot\controllers\privates\StartController;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatTip;
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
        $chat = $this->getTelegramChat();
        $replyMessage = $this->getMessage()->getReplyToMessage();

        if (!isset($chatTipId) && $replyMessage) {
            if ($chat->isGroup()) {
                $toUser = User::findOne([
                    'provider_user_id' => $replyMessage->getFrom()->getId(),
                ]);

                $chatMember = ChatMember::findOne([
                    'chat_id' => $chat->id,
                    'user_id' => $toUser->id,
                ]);

                if ($chatMember->isAnonymousAdministrator()) {
                    $chatMember = ChatMember::findOne([
                        'chat_id' => $chat->id,
                        'status' => ChatMember::STATUS_CREATOR,
                    ]);

                    if (empty($chatMember)) {
                        return [];
                    }

                    $toUser = $chatMember->user;
                    $actionName = 'group-guest/view';
                } elseif ($toUser->isBot()) {
                    return [];
                }

                $chatTip = new ChatTip([
                    'chat_id' => $chat->id,
                    'to_user_id' => $toUser->getId(),
                    'reply_message_id' => $replyMessage->getMessageId(),
                ]);

                $chatTip->save();

                $actionParams = [
                    'id' => $chat->id,
                    'chatTipId' => $chatTip->id,
                ];
            }
        } elseif (isset($chatTipId)) {
            $chatTip = ChatTip::findOne($chatTipId);

            if (!isset($chatTip)) {
                return [];
            }

            $chat = $chatTip->chat;

            if ($this->getTelegramChat()->getChatId() != $chat->getChatId()) {
                return [];
            }

            $walletTransactions = $chatTip->getWalletTransactions()->all();

            foreach ($walletTransactions as $transaction) {
                if ($transaction->anonymity) {
                    $actionName = 'group-guest/view';
                    $actionParams = [
                        'id' => $chat->id,
                        'chatTipId' => $chatTip->id,
                    ];
                    break;
                }
            }

            $toUser = $chatTip->toUser;
        }
        // check if $fromUser is a chat member && $fromUser is not tipping himself
        $fromChatMember = $chat->getChatMemberByUser($fromUser);

        if (isset($fromChatMember) && $chat->isGroup() && !isset($replyMessage)) {
            // tip without reply
            $actionName = 'tip-queue/index';
            $actionParams = [
                'chatId' => $chat->id,
            ];
        } elseif (!isset($toUser) || !isset($fromChatMember) || ($toUser->getId()) == $fromUser->getId()) {
            return [];
        }

        $privateChat = Chat::findOne([
            'chat_id' => $fromUser->provider_user_id,
        ]);

        if (!isset($privateChat)) {
            return [];
        }

        $module = Yii::$app->getModule('bot');
        $module->setChat($privateChat);

        if (!isset($actionName)) {
            $actionName = 'member/id';
            $actionParams = [
                'id' => $chatTip->chat->getChatMemberByUser($toUser)->id,
                'chatTipId' => $chatTip->id,
            ];
        }

        $module->runAction($actionName, $actionParams);

        $module->setChat($chat);

        if ($this->getUpdate()->getCallbackQuery()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }
    }

    /**
     * @param int $chatTipId ChatTip->id
     *
     * @return array
     */
    public function actionTipMessage($chatTipId = null)
    {
        $chatTip = ChatTip::findOne($chatTipId);

        if (!isset($chatTip)) {
            return [];
        }

        $toUser = $chatTip->toUser;
        // find all transactions for tip message
        $walletTransactions = $chatTip->getWalletTransactions()->all();
        // calculate amount according to currency
        $totalAmounts = [];

        $anonymity = false;

        foreach ($walletTransactions as $transaction) {
            if ($transaction->anonymity) {
                $anonymity = true;
            }
            if (!array_key_exists($transaction->currency->code, $totalAmounts)) {
                $totalAmounts[$transaction->currency->code] = $transaction->amount;
            } else {
                $totalAmounts[$transaction->currency->code] = Number::floatAdd($totalAmounts[$transaction->currency->code], $transaction->amount);
            }
        }

        $buttons = [
            [
                [
                    'callback_data' => self::createRoute('index', [
                        'chatTipId' => $chatTip->id,
                    ]),
                    'text' => Emoji::ADD . Emoji::GIFT,
                ],
            ],
        ];

        if (!$anonymity) {
            $buttons[] = [
                [
                    'url' => ExternalLink::getBotStartLink($toUser->provider_user_id, $chatTip->chat->getChatId()),
                    'text' => Yii::t('bot', 'Member View'),
                ],
            ];
        }

        if ($chatTip->message_id) {
            // edit message
            return $this->getResponseBuilder()
                ->editMessage(
                    $chatTip->message_id,
                    $this->render('tip-message', [
                        'totalAmounts' => $totalAmounts,
                        'user' => $toUser,
                        'anonymity' => $anonymity,
                    ]),
                    $buttons,
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
                    'totalAmounts' => $totalAmounts,
                    'user' => $toUser,
                    'anonymity' => $anonymity,
                ]),
                $buttons,
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

            return $response;
        }

        return [];
    }
}
