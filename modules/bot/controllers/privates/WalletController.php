<?php

namespace app\modules\bot\controllers\privates;

use app\models\Currency;
use app\models\Wallet;
use app\models\WalletTransaction;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\User;
use Yii;
use yii\data\Pagination;
use yii\db\ActiveRecord;

/**
 * Class WalletController
 *
 * @package app\modules\bot\controllers\privates
 */
class WalletController extends Controller
{
    /**
     * @param int $page
     *
     * @return array
     */
    public function actionIndex($page = 1)
    {
        $this->getState()->setName(null);

        $query = $this->globalUser->getWallets()
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

        $buttons = [];

        $wallets = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($wallets) {
            foreach ($wallets as $wallet) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('view', [
                        'id' => $wallet->getCurrencyId(),
                    ]),
                    'text' => $wallet->amount . ' ' . $wallet->currency->code,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) {
                return self::createRoute('index', [
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
            [
                'callback_data' => self::createRoute('add'),
                'text' => Emoji::ADD,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                $buttons
            )
            ->build();
    }

    /**
     * @param int $id Currency->id
     *
     * @return array
     */
    public function actionView($id = null)
    {
        $wallet = Wallet::findOne([
            'currency_id' => $id,
            'user_id' => $this->globalUser->id,
        ]);

        if (!$wallet) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('view', [
                    'wallet' => $wallet,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('send-transaction', [
                                'id' => $wallet->getCurrencyId(),
                            ]),
                            'text' => Yii::t('bot', 'Send'),
                            'visible' => $wallet->amount > 0,
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('transactions', [
                                'id' => $wallet->getCurrencyId(),
                            ]),
                            'text' => Yii::t('bot', 'Transactions'),
                            'visible' => $wallet->getTransactions()->exists(),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => self::createRoute('delete', [
                                'id' => $wallet->getCurrencyId(),
                            ]),
                            'text' => Emoji::DELETE,
                            'visible' => ($wallet->amount == 0) && !$wallet->getTransactions()->exists(),
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @param string|null $code Currency->code
     * @param int $page
     *
     * @return array
     */
    public function actionAdd($code = null, $page = 1)
    {
        if ($code) {
            $currency = Currency::findOne([
                'code' => $code,
            ]);

            if ($currency) {
                $wallet = Wallet::findOne([
                    'currency_id' => $currency->id,
                    'user_id' => $this->globalUser->id,
                ]);

                if (!$wallet) {
                    $wallet = new Wallet();
                    $wallet->currency_id = $currency->id;
                    $wallet->user_id = $this->globalUser->id;
                    $wallet->save();
                }

                return $this->actionView($currency->id);
            }
        }

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                if (strlen($text) <= 3) {
                    $currency = Currency::find()
                        ->orFilterWhere(['like', 'code', $text, false])
                        ->one();
                } else {
                    $currency = Currency::find()
                        ->orFilterWhere(['like', 'name', $text . '%', false])
                        ->one();
                }

                if ($currency) {
                    $wallet = Wallet::findOne([
                        'currency_id' => $currency->id,
                        'user_id' => $this->globalUser->id,
                    ]);

                    if (!$wallet) {
                        $wallet = new Wallet();
                        $wallet->currency_id = $currency->id;
                        $wallet->user_id = $this->globalUser->id;
                        $wallet->save();
                    }

                    return $this->actionView($currency->id);
                }
            }
        }

        $this->getState()->setName(self::createRoute('add'));

        $query = Currency::find()
            ->orderBy([
                'code' => SORT_ASC,
            ]);

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $currencies = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $buttons = [];

        if ($currencies) {
            foreach ($currencies as $currency) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('add', [
                        'code' => $currency->code,
                    ]),
                    'text' => $currency->code . ' - ' . $currency->name,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) {
                return self::createRoute('add', [
                    'page' => $page,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute(),
                'text' => Emoji::BACK,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('add'),
                $buttons
            )
            ->build();
    }

    /**
     * @param int $id Currency->id
     *
     * @return array
     */
    public function actionDelete($id = null)
    {
        $wallet = Wallet::findOne([
            'currency_id' => $id,
            'user_id' => $this->globalUser->id,
            'amount' => 0,
        ]);

        if ($wallet) {
            $wallet->delete();

            return $this->actionIndex();
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param int $id Currency->id
     *
     * @return array
     */
    public function actionSendTransaction($id = null)
    {
        $wallet = Wallet::findOne([
            'currency_id' => $id,
            'user_id' => $this->globalUser->id,
        ]);

        if (!$wallet) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('input-to-user', [
            'id' => $id,
        ]));

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'id' => $id,
                ]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('send-transaction'),
                $buttons
            )
            ->build();
    }

    /**
     * @param int $id Currency->id
     *
     * @return array
     */
    public function actionInputToUser($id = null)
    {
        if ($text = $this->getMessage()->getText()) {
            if (preg_match('/(?:^@(?:[A-Za-z0-9][_]{0,1})*[A-Za-z0-9]+)/i', $text, $matches)) {
                $username = ltrim($matches[0], '@');
            }
        }

        if (!isset($username)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $toUser = User::find()
            ->where([
                'provider_user_name' => $username,
            ])
            ->one();

        if (!isset($toUser)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        // check if to_user user has wallet
        $toUserWallet = Wallet::findOne([
            'currency_id' => $id,
            'user_id' => $toUser->getId(),
        ]);

        if (!$toUserWallet) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->actionInputAmount($toUser->getId(), $id);
    }

    /**
     * @param int $to_user_id
     * @param int $id Currency->id
     *
     * @return array
     */
    public function actionInputAmount($to_user_id = null, $id = null)
    {
        $this->getState()->setName(self::createRoute('input-amount', [
            'to_user_id' => $to_user_id,
            'id' => $id,
        ]));

        $amount = 0;
        if ($this->getUpdate()->getMessage()) {
            if ((float)$this->getUpdate()->getMessage()->getText()) {
                $amount = (float)$this->getUpdate()->getMessage()->getText();
                $amount = number_format($amount, 2, '.', '');
                $amount  = $amount < 0.01 ? 0 : $amount;
            }
        }

        $fromUser = User::find()
            ->where([
                'id' => $this->globalUser->id,
            ])
            ->one();

        $toUser = User::find()
            ->where([
                'id' => $to_user_id,
            ])
            ->one();

        $currency = Currency::find()
            ->where([
                'id' => $id,
            ])
            ->one();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('confirm-transaction', [
                    'amount' => $amount,
                    'fee' => WalletTransaction::TRANSACTION_FEE,
                    'fromUser' => $fromUser,
                    'toUser' => $toUser,
                    'currency' => $currency,

                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('confirm-transaction', [
                                'id' => $id,
                                'to_user_id' => $to_user_id,
                                'amount' => $amount,
                            ]),
                            'text' => 'Confirm',
                            'visible' => $amount > 0,
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('view', [
                                'id' => $id,
                            ]),
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

    /**
     * @param int $id Currency->id
     * @param int $to_user_id
     * @param int $amount
     *
     * @return array
     */
    public function actionConfirmTransaction($id = null, $to_user_id = null, $amount = null)
    {
        $fromUserWallet = Wallet::findOne([
            'currency_id' => $id,
            'user_id' => $this->globalUser->id,
        ]);

        $toUserWallet = Wallet::findOne([
            'currency_id' => $id,
            'user_id' => $to_user_id,
        ]);

        if (($fromUserWallet->amount - $amount - WalletTransaction::TRANSACTION_FEE) < 0) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $transaction = ActiveRecord::getDb()->beginTransaction();
        try {
            $walletTransaction = new WalletTransaction();
            $walletTransaction->currency_id = $id;
            $walletTransaction->from_user_id = $this->globalUser->id;
            $walletTransaction->to_user_id = $to_user_id;
            $walletTransaction->amount = $amount + WalletTransaction::TRANSACTION_FEE;
            $walletTransaction->fee = WalletTransaction::TRANSACTION_FEE;
            $walletTransaction->type = 0;
            $walletTransaction->anonymity = 0;
            $walletTransaction->created_at = time();

            if ($walletTransaction->save()) {
                $toUserWallet->amount += $amount;
                $toUserWallet->save();
                $fromUserWallet->amount -= $amount + WalletTransaction::TRANSACTION_FEE;
                $fromUserWallet->save();
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
        }

        return $this->actionView($id);
    }

    /**
     * @param int $id Currency->id
     * @param int $page
     *
     * @return array
     */
    public function actionTransactions($id = null, $page = 1)
    {
        $wallet = Wallet::findOne([
            'currency_id' => $id,
            'user_id' => $this->globalUser->id,
        ]);

        if (!$wallet) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $query = $wallet->getTransactions();

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $buttons = [];

        $transactions = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $currency = $wallet->getCurrency()->one();

        if ($transactions) {
            foreach ($transactions as $transaction) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('transaction', [
                        'id' => $transaction->getId(),
                    ]),
                    'text' => $transaction->getAmount() . ' ' . $currency->code . ' / ' . date('Y-m-d H:i:s', $transaction->getCreatedAt()),
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($id) {
                return self::createRoute('transactions', [
                    'id' => $id,
                    'page' => $page,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'id' => $id,
                ]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('transactions'),
                $buttons
            )
            ->build();
    }

    /**
     * @param int $id Currency->id
     *
     * @return array
     */
    public function actionTransaction($id = null)
    {
        $transaction = WalletTransaction::findOne($id);

        if (!isset($transaction)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $fromUser = User::find()
            ->where([
                'id' => $transaction->getFromUserId(),
            ])
            ->one();

        $toUser = User::find()
            ->where([
                'id' => $transaction->getToUserId(),
            ])
            ->one();

        $currency = $transaction->getCurrency()->one();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('transaction', [
                    'transaction' => $transaction,
                    'fromUser' => $fromUser,
                    'toUser' => $toUser,
                    'currency' => $currency,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('transactions', [
                                'id' => $transaction->getCurrencyId(),
                            ]),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => self::createRoute('delete-transaction', [
                                'id' => $id,
                            ]),
                            'text' => Emoji::DELETE,
                        ],
                    ],
                ]
            )
            ->build();
    }
}
