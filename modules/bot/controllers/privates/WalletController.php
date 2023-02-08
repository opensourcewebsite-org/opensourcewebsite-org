<?php

namespace app\modules\bot\controllers\privates;

use app\components\helpers\TimeHelper;
use DateTime;
use DateTimeZone;
use app\helpers\Number;
use app\models\Currency;
use app\models\Wallet;
use app\models\WalletTransaction;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\ChatTipWalletTransaction;
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
        $wallet = $this->getGlobalUser()->getWalletByCurrencyId($id);

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
                            'visible' => Number::isFloatGreater($wallet->amount, 0),
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
                            'visible' => (Number::isFloatEqual($wallet->amount, 0)) && !$wallet->getTransactions()->exists(),
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
                $this->getGlobalUser()->getWalletByCurrencyId($currency->id);

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
                    $this->getGlobalUser()->getWalletByCurrencyId($currency->id);
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
        $this->getState()->setName(self::createRoute('input-to-user'));

        $this->getState()->setIntermediateModel(new WalletTransaction([
            'from_user_id' => $this->getTelegramUser()->getUserId(),
            'currency_id' => $id,
            'type' => 0,
            'anonymity' => 0,
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
     * @return array
     */
    public function actionInputToUser()
    {
        $walletTransaction = $this->getState()->getIntermediateModel(WalletTransaction::class);

        if (!isset($walletTransaction)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

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

        $toUser = User::findOne(['provider_user_name' => $username]);

        // check if user exists or user is bot
        if (!isset($toUser) || $toUser->isBot()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $walletTransaction->to_user_id = $toUser->getId();
        $this->getState()->setIntermediateModel($walletTransaction);

        return $this->actionInputAmount();
    }

    /**
     * @return array
     */
    public function actionInputAmount()
    {
        $this->getState()->setName(self::createRoute('input-amount'));

        $walletTransaction = $this->getState()->getIntermediateModel(WalletTransaction::class);

        if (!isset($walletTransaction)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $amount = 0;
        if ($this->getUpdate()->getMessage()) {
            if ($amount = (float)$this->getUpdate()->getMessage()->getText()) {
                $amount = number_format($amount, 2, '.', '');

                if (!$this->getGlobalUser()->getWalletByCurrencyId($walletTransaction->currency_id)->hasAmount($amount)) {
                    return $this->getResponseBuilder()
                        ->answerCallbackQuery()
                        ->build();
                }

                if (Number::isFloatLower($amount, WalletTransaction::MIN_AMOUNT)) {
                    $amount = WalletTransaction::MIN_AMOUNT;
                }

                $walletTransaction->amount = $amount;
                $this->getState()->setIntermediateModel($walletTransaction);
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('confirm-transaction', [
                    'walletTransaction' => $walletTransaction,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('confirm-transaction'),
                            'text' => 'Confirm',
                            'visible' => $amount > 0,
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('view', [
                                'id' => $walletTransaction->currency_id,
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
     * @return array
     */
    public function actionConfirmTransaction()
    {
        $walletTransaction = $this->getState()->getIntermediateModel(WalletTransaction::class);

        if (!isset($walletTransaction)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ($this->getGlobalUser()->createTransaction($walletTransaction)) {
            $this->getState()->clearIntermediateModel(WalletTransaction::class);

            return $this->actionView($walletTransaction->currency_id);
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param int $id Currency->id
     * @param int $page
     *
     * @return array
     */
    public function actionTransactions($id = null, $page = 1)
    {
        $wallet = $this->getTelegramUser()->getWalletByCurrencyId($id);

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

        $walletTransactions = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy([
                'created_at' => SORT_DESC,
            ])
            ->all();

        $currency = $wallet->currency;

        if ($walletTransactions) {
            foreach ($walletTransactions as $transaction) {
                $date = new DateTime();
                $date->setTimezone(new DateTimeZone(TimeHelper::getTimezoneByOffset($this->getGlobalUser()->timezone)));
                $date->setTimestamp($transaction->getCreatedAt());

                $buttons[][] = [
                    'callback_data' => self::createRoute('transaction', [
                        'id' => $transaction->getId(),
                    ]),
                    'text' => $transaction->getAmount() . ' ' . $currency->code . ' / ' . $date->format('Y/m/d H:i:s'),
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
     * @param int $id WalletTransaction->id
     *
     * @return array
     */
    public function actionTransaction($id = null)
    {
        $walletTransaction = WalletTransaction::findOne($id);

        if (!isset($walletTransaction)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        if ($walletTransaction->fromUser->id != $this->getGlobalUser()->id) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('transaction', [
                    'walletTransaction' => $walletTransaction,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('transactions', [
                                'id' => $walletTransaction->getCurrencyId(),
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
     * @param int $walletTransactionId WalletTransaction->id
     *
     * @return array
     */
    public function actionDeleteTransaction($walletTransactionId = null)
    {
        $walletTransaction = WalletTransaction::findOne(['id' => $walletTransactionId]);
        $chatTipWalletTransaction = ChatTipWalletTransaction::findOne(['transaction_id' => $walletTransactionId]);

        if ($walletTransaction && $chatTipWalletTransaction) {
            $transaction = ActiveRecord::getDb()->beginTransaction();
            try {
                $chatTipWalletTransaction->delete();
                $walletTransaction->delete();
                $transaction->commit();
                return $this->actionTransactions($walletTransaction->currency_id);
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
