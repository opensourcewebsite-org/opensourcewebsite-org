<?php

namespace app\modules\bot\controllers\privates;

use app\models\Currency;
use app\models\Wallet;
use app\models\WalletTransaction;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\User;
use Yii;
use yii\data\Pagination;
use yii\db\ActiveRecord;

/**
 * Class GroupTipController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupTipController extends Controller
{
    /**
     * @param int $chatId Chat->id
     * @param int $toUserId User->id
     * @param int $replyMessageId Message->id
     * @param int $messageId Message->id
     *
     * @return array
     */
    public function actionView($chatId = null, $toUserId = null, $replyMessageId = null, $messageId = null)
    {
        $chat = Chat::findOne($chatId);
        $toUser = User::findOne($toUserId);

        if (!isset($chat) || !$chat->isGroup() || !isset($toUser)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(json_encode([
            'chatId' => $chatId,
            'toUserId' => $toUserId,
            'replyMessageId' => $replyMessageId,
            'messageId' => $messageId,
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
        $fromUser = $this->getTelegramUser();
        $toUser = User::findOne($state->toUserId);

        if ($currencyId) {
            $fromUserWallet = Wallet::findOne([
                'currency_id' => $currencyId,
                'user_id' => $fromUser->getUserId(),
            ]);

            $toUserWallet = Wallet::findOne([
                'currency_id' => $currencyId,
                'user_id' => $toUser->getUserId(),
            ]);

            $currency = Currency::findOne([
                'id' => $currencyId,
            ]);

            if (!$fromUserWallet || !$toUserWallet || !$currency) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery()
                    ->build();
            }

            return $this->actionSetAmount($state->chatId, $state->toUserId, $currency->code, $state->replyMessageId, $state->messageId);
        }

        $query = Wallet::find()
            ->where(['user_id' => $fromUser->getUserId()])
            ->andWhere(['>', Wallet::tableName() . '.amount', 0])
            ->orderByCurrencyCode();

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
     * @param int $chatId Chat->id
     * @param int $toUserId User->id
     * @param string $code Currency->code
     * @param int $replyMessageId Message->id
     * @param int $messageId Message->id
     *
     * @return array
     */
    public function actionSetAmount($chatId = null, $toUserId = null, $code = null, $replyMessageId = null, $messageId = null)
    {
        $this->getState()->setName(self::createRoute('set-amount', [
            'chatId' => $chatId,
            'toUserId' => $toUserId,
            'code' => $code,
            'replyMessageId' => $replyMessageId,
            'messageId' => $messageId,
        ]));

        $currency = Currency::findOne([
            'code' => $code,
        ]);

        $fromUserWallet = Wallet::findOne([
            'currency_id' => $currency->id,
            'user_id' => $this->getTelegramUser()->getUserId(),
        ]);

        if ($this->getUpdate()->getMessage()) {
            if ((float)$this->getUpdate()->getMessage()->getText()) {
                $amount = (float)$this->getUpdate()->getMessage()->getText();
                $amount = number_format($amount, 2, '.', '');
                $amount = $amount < 0.01 ? 0 : $amount;

                if ($amount > 0) {
                    if (($fromUserWallet->amount - $amount - WalletTransaction::TRANSACTION_FEE) < 0) {
                        return $this->getResponseBuilder()
                            ->answerCallbackQuery()
                            ->build();
                    }

                    $this->getState()->setName(json_encode([
                        'chatId' => $chatId,
                        'toUserId' => $toUserId,
                        'code' => $code,
                        'amount' => $amount,
                        'replyMessageId' => $replyMessageId,
                        'messageId' => $messageId,
                    ]));

                    $toUser = User::findOne($toUserId);

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
                                        'callback_data' => self::createRoute('choose-currency'),
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
                            'callback_data' => self::createRoute('view', [
                                'chatId' => $chatId,
                                'toUserId' => $toUserId,
                                'messageId' => $messageId,
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
        $fromUser = $this->getTelegramUser();
        $toUser = User::findOne($state->toUserId);

        $currency = Currency::findOne([
            'code' => $state->code,
        ]);

        if ($currency) {
            $fromUserWallet = Wallet::findOne([
                'currency_id' => $currency->id,
                'user_id' => $fromUser->getUserId(),
            ]);

            $toUserWallet = Wallet::findOne([
                'currency_id' => $currency->id,
                'user_id' => $toUser->getUserId(),
            ]);

            $transaction = ActiveRecord::getDb()->beginTransaction();
            try {
                $walletTransaction = new WalletTransaction();
                $walletTransaction->currency_id = $fromUserWallet->getCurrencyId();
                $walletTransaction->from_user_id = $fromUserWallet->getUserId();
                $walletTransaction->to_user_id = $toUserWallet->getUserId();
                $walletTransaction->amount = $state->amount + WalletTransaction::TRANSACTION_FEE;
                $walletTransaction->fee = WalletTransaction::TRANSACTION_FEE;
                $walletTransaction->type = 0;
                $walletTransaction->anonymity = 0;
                $walletTransaction->created_at = time();

                if ($walletTransaction->save()) {
                    $toUserWallet->amount += $state->amount;
                    $toUserWallet->save();
                    $fromUserWallet->amount -= $state->amount + WalletTransaction::TRANSACTION_FEE;
                    $fromUserWallet->save();
                }

                $transaction->commit();

                $thisChat = $this->getTelegramChat();
                $module = Yii::$app->getModule('bot');
                $module->setChat(Chat::findOne($state->chatId));
                if (isset($state->messageId)) {
                    // update tip message
                    $response = $module->runAction('tip/update-tip-message', [
                        'chatId' => $state->chatId,
                        'walletTransaction' => $walletTransaction,
                        'messageId' => $state->messageId,
                    ]);
                } else {
                    // send tip message
                    $response = $module->runAction('tip/show-tip-message', [
                        'chatId' => $state->chatId,
                        'walletTransaction' => $walletTransaction,
                        'replyMessageId' => $state->replyMessageId,
                    ]);
                }

                $module->setChat($thisChat);

                // send response to private chat
                if ($response) {
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
            } catch (\Throwable $e) {
                $transaction->rollBack();
                Yii::error($e->getMessage());
            }
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }
}
