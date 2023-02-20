<?php

namespace app\modules\bot\controllers\groups;

use app\helpers\Number;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\controllers\privates\DeleteMessageController;
use app\modules\bot\controllers\privates\MemberController;
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

        if (!isset($chatTipId)) {
            $chat = $this->getTelegramChat();

            if ($chat->isGroup() && $replyMessage = $this->getMessage()->getReplyToMessage()) {
                $toUser = User::findOne([
                    'provider_user_id' => $replyMessage->getFrom()->getId(),
                    'is_bot' => 0,
                ]);

                $chatMember = ChatMember::findOne([
                    'chat_id' => $chat->id,
                    'user_id' => $toUser->id,
                ]);

                if ($chatMember->isAnonymousAdministrator()) {
                    return [];
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
                return [];
            }

            $chat = $chatTip->chat;

            if ($this->getTelegramChat()->getChatId() != $chat->getChatId()) {
                return [];
            }

            $toUser = $chatTip->toUser;
        }
        // check if $fromUser is a chat member && $fromUser is not tipping himself
        $fromChatMember = $chat->getChatMemberByUser($fromUser);

        if (isset($toUser) && isset($fromChatMember) && ($toUser->getId()) != $fromUser->getId()) {
            $this->getState()->setName(json_encode($chatTip->id));

            $fromUser->sendMessage(
                $this->render('/privates/tip', [
                    'chat' => $chat,
                    'user' => $toUser,
                ]),
                [
                    [
                        [
                            'callback_data' => MemberController::createRoute('id', [
                                'id' => $chatTip->chat->getChatMemberByUser($toUser)->id,
                                'chatTipId' => $chatTip->id,
                            ]),
                            'text' => Yii::t('bot', 'Send a Tip'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => DeleteMessageController::createRoute(),
                            'text' => Yii::t('bot', 'CANCEL'),
                        ],
                    ],
                ]
            );
        }

        return [];
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

        foreach ($walletTransactions as $transaction) {
            if (!array_key_exists($transaction->currency->code, $totalAmounts)) {
                $totalAmounts[$transaction->currency->code] = $transaction->amount;
            } else {
                $totalAmounts[$transaction->currency->code] = Number::floatAdd($totalAmounts[$transaction->currency->code], $transaction->amount);
            }
        }

        if ($chatTip->message_id) {
            // edit message
            return $this->getResponseBuilder()
                ->editMessage(
                    $chatTip->message_id,
                    $this->render('tip-message', [
                        'totalAmounts' => $totalAmounts,
                        'user' => $toUser,
                    ]),
                    [
                        [
                            [
                                'url' => ExternalLink::getBotStartLink($toUser->provider_user_id),
                                'text' => Yii::t('bot', 'User View'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('tip-message', [
                                    'chatTipId' => $chatTipId,
                                ]),
                                'text' => Emoji::REFRESH,
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('index', [
                                    'chatTipId' => $chatTip->id,
                                ]),
                                'text' => Yii::t('bot', 'Add a Tip'),
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
                    'totalAmounts' => $totalAmounts,
                    'user' => $toUser,
                ]),
                [
                    [
                        [
                            'url' => ExternalLink::getBotStartLink($toUser->provider_user_id),
                            'text' => Yii::t('bot', 'User View'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('tip-message', [
                                'chatTipId' => $chatTipId,
                            ]),
                            'text' => Emoji::REFRESH,
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'chatTipId' => $chatTip->id,
                            ]),
                            'text' => Yii::t('bot', 'Add a Tip'),
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

            return $response;
        }

        return [];
    }
}
