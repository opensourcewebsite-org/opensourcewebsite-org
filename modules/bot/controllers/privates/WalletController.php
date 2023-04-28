<?php

namespace app\modules\bot\controllers\privates;

use app\helpers\Number;
use app\models\Currency;
use app\models\Wallet;
use app\models\WalletTransaction;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\ChatTipWalletTransaction;
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
    public const SEND_TIP_STATE = 1;
    public const SEND_MONEY_STATE = 2;

    /**
     * @param int $page
     *
     * @return array
     */
    public function actionIndex($page = 1)
    {
        $this->getState()->clearInputRoute();

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

        $walletTransaction = new WalletTransaction();
        $walletTransaction->from_user_id = $this->getTelegramUser()->getUserId();
        $walletTransaction->currency_id = $wallet->getCurrencyId();
        $this->getState()->setItem($walletTransaction);

        $this->getState()->setBackRoute(self::createRoute('view', [
            'id' => $wallet->getCurrencyId(),
        ]));

        $p2pExchangeLink = Yii::$app->settings->link_p2p_exchange;

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('view', [
                    'wallet' => $wallet,
                ]),
                [
                    [
                        [
                            'callback_data' => TransactionController::createRoute('index'),
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
                            'url' => $p2pExchangeLink,
                            'text' => Yii::t('bot', 'P2P Exchange'),
                            'visible' => (bool)$p2pExchangeLink,
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('index'),
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

        $this->getState()->setInputRoute(self::createRoute('add'));

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
                'callback_data' => self::createRoute('index'),
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
                $buttons[][] = [
                    'callback_data' => self::createRoute('transaction', [
                        'id' => $transaction->getId(),
                    ]),
                    'text' => ($this->getTelegramUser()->getUserId() == $transaction->getFromUserID() ? '-' : '+') . $transaction->getAmount() . ' ' . $currency->code . ' - ' . Yii::$app->formatter->asDateTime($transaction->getCreatedAtByUser()),
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

        $this->getState()->clearInputRoute();

        if ($walletTransaction->fromUser->id != $this->getGlobalUser()->id) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('transaction', [
                    'walletTransaction' => $walletTransaction,
                    'timezone' => $this->getGlobalUser()->timezone,
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
