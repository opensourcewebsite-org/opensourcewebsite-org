<?php

namespace app\modules\bot\controllers\privates;

use app\models\Currency;
use app\models\WalletTransaction;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatTip;
use app\modules\bot\models\ChatTipWalletTransaction;
use app\modules\bot\models\User;
use Yii;
use yii\data\Pagination;

/**
 * Class SendGroupTipController
 *
 * @package app\modules\bot\controllers\privates
 */
class SendGroupTipController extends Controller
{
    /**
     * @param int $chatTipId ChatTip->id
     * @param int $replyMessageId Message->id
     *
     * @return array
     */
    public function actionIndex($chatTipId = null, $replyMessageId = null)
    {
        $chatTip = ChatTip::findOne($chatTipId);
        $chat = Chat::findOne($chatTip->chat_id);
        $toUser = User::findOne($chatTip->to_user_id);

        if (!isset($chat) || !$chat->isGroup() || !isset($toUser)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(json_encode([
            'chatTipId' => $chatTipId,
            'replyMessageId' => $replyMessageId,
        ]));

        return $this->actionChooseWallet();
    }

    /**
     * @param int|null $currencyId Currency->id
     * @param int $page
     *
     * @return array
     */
    public function actionChooseWallet($currencyId = null, $page = 1)
    {
        $state = json_decode($this->getState()->getName());

        if ($currencyId) {
            $currency = Currency::findOne([
                'id' => $currencyId,
            ]);

            if (!isset($currency)) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery()
                    ->build();
            }

            return $this->actionSetAmount($state->chatTipId, $currency->code, $state->replyMessageId);
        }

        $query = $this->getGlobalUser()->getWalletsWithPositiveBalance();

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $wallets = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $buttons = [];

        if ($wallets) {
            foreach ($wallets as $wallet) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('choose-wallet', [
                        'currencyId' => $wallet->getCurrencyId(),
                    ]),
                    'text' => $wallet->amount . ' ' . $wallet->currency->code,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) {
                return self::createRoute('choose-wallet', [
                    'page' => $page,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('choose-wallet'),
                $buttons
            )
            ->build();
    }

    /**
     * @param int $chatTipId ChatTip->id
     * @param string $code Currency->code
     * @param int $replyMessageId Message->id
     *
     * @return array
     */
    public function actionSetAmount($chatTipId = null, $code = null, $replyMessageId = null)
    {
        $this->getState()->setName(self::createRoute('set-amount', [
            'chatTipId' => $chatTipId,
            'code' => $code,
            'replyMessageId' => $replyMessageId,
        ]));

        $currency = Currency::findOne([
            'code' => $code,
        ]);

        if ($currency) {
            $fromUserWallet = $this->getGlobalUser()->getWalletByCurrencyId($currency->id);

            if ($this->getUpdate()->getMessage()) {
                if ((float)$this->getUpdate()->getMessage()->getText()) {
                    $amount = (float)$this->getUpdate()->getMessage()->getText();
                    $amount = number_format($amount, 2, '.', '');

                    if (!$fromUserWallet->hasAmount($amount)) {
                        return $this->getResponseBuilder()
                            ->answerCallbackQuery()
                            ->build();
                    }

                    if ($amount < WalletTransaction::MIN_AMOUNT) {
                        $amount = WalletTransaction::MIN_AMOUNT;
                    }

                    $this->getState()->setName(json_encode([
                        'chatTipId' => $chatTipId,
                        'code' => $code,
                        'amount' => $amount,
                        'replyMessageId' => $replyMessageId,
                    ]));

                    $toUser = ChatTip::findOne($chatTipId)->toUser;

                    return $this->getResponseBuilder()
                        ->editMessageTextOrSendMessage(
                            $this->render('confirm-transaction', [
                                'toUser' => $toUser,
                                'amount' => $amount,
                                'code' => $code,
                            ]),
                            [
                                [
                                    [
                                        'callback_data' => self::createRoute('confirm-transaction'),
                                        'text' => 'Confirm',
                                    ],
                                ],
                                [
                                    [
                                        'callback_data' => self::createRoute('choose-wallet'),
                                        'text' => Emoji::BACK,
                                    ],
                                    [
                                        'callback_data' => MenuController::createRoute(),
                                        'text' => Emoji::MENU,
                                    ],
                                ],
                            ]
                        )
                        ->build();
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-amount', [
                    'maxAmount' => $fromUserWallet->amount - WalletTransaction::TRANSACTION_FEE,
                    'code' => $code,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'chatTipId' => $chatTipId,
                                'replyMessageId' => $replyMessageId,
                            ]),
                            'text' => Emoji::DELETE,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionConfirmTransaction()
    {
        $state = json_decode($this->getState()->getName());
        $chatTip = ChatTip::findOne($state->chatTipId);
        $toUser = $chatTip->toUser;

        $currency = Currency::findOne([
            'code' => $state->code,
        ]);

        if ($currency) {
            $fromUserWallet = $this->getGlobalUser()->getWalletByCurrencyId($currency->id);
            $toUserWallet = $toUser->getWalletByCurrencyId($currency->id);

            $walletTransaction = new WalletTransaction();
            $walletTransaction->currency_id = $fromUserWallet->getCurrencyId();
            $walletTransaction->from_user_id = $fromUserWallet->getUserId();
            $walletTransaction->to_user_id = $toUserWallet->getUserId();
            $walletTransaction->amount = $state->amount;
            $walletTransaction->type = 0;
            $walletTransaction->anonymity = 0;
            $walletTransaction->created_at = time();

            if (!$this->getGlobalUser()->createTransaction($walletTransaction)) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery()
                    ->build();
            }

            $thisChat = $this->getTelegramChat();

            $module = Yii::$app->getModule('bot');
            $module->setChat(Chat::findOne($chatTip->chat_id));

            // create new ChatTipWalletTransaction record
            $chatTipWalletTransaction = new ChatTipWalletTransaction([
                'chat_tip_id' => $chatTip->id,
                'transaction_id' => $walletTransaction->id,
            ]);

            $chatTipWalletTransaction->save();

            if (isset($chatTip->message_id)) {
                // update tip message
                $response = $module->runAction('tip/update-tip-message', [
                    'chatTipWalletTransactionId' => $chatTipWalletTransaction->id,
                ]);
            } else {
                // send tip message
                $response = $module->runAction('tip/show-tip-message', [
                    'chatTipWalletTransactionId' => $chatTipWalletTransaction->id,
                    'replyMessageId' => $state->replyMessageId,
                ]);
            }

            $module->setChat($thisChat);

            // send response to private chat
            if ($response) {
                $toUser->sendMessage(
                    $this->render('receiver-privates-success', [
                        'walletTransaction' => $walletTransaction,
                        'toUserWallet' => $toUserWallet,
                    ]),
                    []
                );

                return $this->getResponseBuilder()
                    ->editMessageTextOrSendMessage(
                        $this->render('success', [
                            'walletTransaction' => $walletTransaction,
                        ]),
                        [
                            [
                                [
                                    'callback_data' => MenuController::createRoute(),
                                    'text' => Emoji::MENU,
                                ],
                            ],
                        ]
                    )
                    ->build();
            }
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }
}
