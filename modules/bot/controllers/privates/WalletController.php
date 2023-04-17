<?php

namespace app\modules\bot\controllers\privates;

use app\helpers\Number;
use app\models\Currency;
use app\models\User as GlobalUser;
use app\models\Wallet;
use app\models\WalletTransaction;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatTip;
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
    public const SEND_TIP_STATE = 1;
    public const SEND_MONEY_STATE = 2;

    /**
     * @param int $page
     *
     * @return array
     */
    public function actionIndex($page = 1, $useState = 0)
    {
        $this->getState()->setName(null);

        $query = $this->globalUser->getWallets()
            ->orderByCurrencyCode();

        if ($useState) {
            $query = $this->globalUser->getWalletsWithPositiveBalance()
            ->orderByCurrencyCode();
        }

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
                        'useState' => $useState,
                    ]),
                    'text' => $wallet->amount . ' ' . $wallet->currency->code,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($useState) {
                return self::createRoute('index', [
                    'page' => $page,
                    'useState' => $useState,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        if ($useState) {
            $chatTip = $this->getState()->getIntermediateModel(ChatTip::class);
            $chatMember = $this->getState()->getIntermediateModel(ChatMember::class);
        }

        $buttons[] = [
            [
                'callback_data' => MemberController::createRoute('id', [
                    'id' => isset($chatMember) ? $chatMember->id : null,
                    'chatTipId' => isset($chatTip) ? $chatTip->id : null,
                ]),
                'text' => Emoji::BACK,
                'visible' => $useState > 0,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'callback_data' => self::createRoute('add', [
                    'useState' => $useState,
                ]),
                'text' => Emoji::ADD,
                'visible' => empty($useState),
            ],
        ];

        $viewName = 'index';

        if ($useState) {
            $viewName = 'select-wallet';

            if (count($wallets) < 1) {
                $viewName = 'no-wallet-with-positive-balance';
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render($viewName),
                $buttons
            )
            ->build();
    }

    /**
     * @param int $id Currency->id
     *
     * @return array
     */
    public function actionView($id = null, $useState = 0)
    {
        $wallet = $this->getGlobalUser()->getWalletByCurrencyId($id);

        if ($useState) {
            return $this->actionSendTransaction($wallet->getCurrencyId(), $useState);
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
                                'useState' => $useState,
                            ]),
                            'text' => Yii::t('bot', 'Send'),
                            'visible' => Number::isFloatGreater($wallet->amount, 0),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('transactions', [
                                'id' => $wallet->getCurrencyId(),
                                'useState' => $useState,
                            ]),
                            'text' => Yii::t('bot', 'Transactions'),
                            'visible' => $wallet->getTransactions()->exists(),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'useState' => $useState,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => self::createRoute('delete', [
                                'id' => $wallet->getCurrencyId(),
                                'useState' => $useState,
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
    public function actionAdd($code = null, $page = 1, $useState = 0)
    {
        if ($code) {
            $currency = Currency::findOne([
                'code' => $code,
            ]);

            if ($currency) {
                $this->getGlobalUser()->getWalletByCurrencyId($currency->id);

                return $this->actionView($currency->id, $useState);
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
                    return $this->actionView($currency->id, $useState);
                }
            }
        }

        $this->getState()->setName(self::createRoute('add', [
            'useState' => $useState,
        ]));

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
                        'useState' => $useState,
                    ]),
                    'text' => $currency->code . ' - ' . $currency->name,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($useState) {
                return self::createRoute('add', [
                    'page' => $page,
                    'useState' => $useState,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('index', [
                    'useState' => $useState,
                ]),
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
    public function actionDelete($id = null, $useState = 0)
    {
        $wallet = Wallet::findOne([
            'currency_id' => $id,
            'user_id' => $this->globalUser->id,
            'amount' => 0,
        ]);

        if ($wallet) {
            $wallet->delete();

            return $this->actionIndex(1, $useState);
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
    public function actionSendTransaction($id = null, $useState = 0)
    {
        $this->getState()->setName(self::createRoute('set-to-user', [
            'useState' => $useState,
        ]));

        $currency = Currency::findOne($id);

        if (!$currency) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $transactionData = [
            'from_user_id' => $this->getTelegramUser()->getUserId(),
            'currency_id' => $id,
            'type' => 0,
            'anonymity' => 0,
        ];

        switch ($useState) {
            case self::SEND_MONEY_STATE:
                $chatMember = $this->getState()->getIntermediateModel(ChatMember::class);
                if ($chatMember) {
                    $toUser = $chatMember->user->globalUser;
                }
                break;
            case self::SEND_TIP_STATE:
                $chatTip = $this->getState()->getIntermediateModel(ChatTip::class);
                if ($chatTip) {
                    $toUser = $chatTip->toUser->globalUser;
                }
                break;
        }

        if (isset($toUser)) {
            $transactionData['to_user_id'] = $toUser->id;
        }

        $this->getState()->setIntermediateModel(new WalletTransaction($transactionData));

        if (isset($toUser)) {
            return $this->actionInputAmount($useState);
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'id' => $id,
                    'useState' => $useState,
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
    public function actionSetToUser($useState = 0)
    {
        $walletTransaction = $this->getState()->getIntermediateModel(WalletTransaction::class);

        if (!isset($walletTransaction)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $text = $this->getMessage()->getText();

        if (preg_match('/(?:^@(?:[A-Za-z0-9][_]{0,1})*[A-Za-z0-9]+)/i', $text, $matches)) {
            $username = ltrim($matches[0], '@');
            $toBotUser = User::findOne(['provider_user_name' => $username]);
        }

        // check if user exists or user is bot
        if (!isset($toBotUser) || $toBotUser->isBot()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ($toBotUser->getUserId() == $this->getTelegramUser()->getUserId()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $walletTransaction->to_user_id = $toBotUser->getUserId();
        $this->getState()->setIntermediateModel($walletTransaction);

        return $this->actionInputAmount($useState);
    }

    /**
     * @return array
     */
    public function actionInputAmount($useState = 0)
    {
        $this->getState()->setName(self::createRoute('input-amount', [
            'useState' => $useState,
        ]));

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
                            'callback_data' => self::createRoute('confirm-transaction', [
                                'useState' => $useState,
                            ]),
                            'text' => Yii::t('bot', 'CONFIRM'),
                            'visible' => $amount > 0,
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('view', [
                                'id' => $walletTransaction->currency_id,
                                'useState' => $useState,
                            ]),
                            'text' => Emoji::BACK,
                            'visible' => !isset($useState),
                        ],
                        [
                            'callback_data' => self::createRoute('index', [
                                'useState' => $useState,
                            ]),
                            'text' => Emoji::BACK,
                            'visible' => isset($useState),
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
    public function actionConfirmTransaction($useState = 0)
    {
        $walletTransaction = $this->getState()->getIntermediateModel(WalletTransaction::class);

        if (!isset($walletTransaction)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ($this->getGlobalUser()->createTransaction($walletTransaction)) {
            $this->getState()->clearIntermediateModel(WalletTransaction::class);

            $walletTransaction->toUser->botUser->sendMessage(
                $this->render('receiver-privates-success', [
                    'walletTransaction' => $walletTransaction,
                    'toUserWallet' => $walletTransaction->toUser->botUser->getWalletByCurrencyId($walletTransaction->currency->id),
                ]),
                []
            );

            if ($useState == self::SEND_TIP_STATE) {
                $chatTip = $this->getState()->getIntermediateModel(ChatTip::class);

                if ($chatTip) {

                    // create new ChatTipWalletTransaction record
                    $chatTipWalletTransaction = new ChatTipWalletTransaction([
                        'chat_tip_id' => $chatTip->id,
                        'transaction_id' => $walletTransaction->id,
                    ]);

                    $chatTipWalletTransaction->save();

                    $thisChat = $this->chat;
                    $module = Yii::$app->getModule('bot');
                    $module->setChat(Chat::findOne($chatTip->chat_id));

                    $response = $module->runAction('tip/tip-message', [
                        'chatTipId' => $chatTip->id,
                    ]);

                    $module->setChat($thisChat);
                }
            }

            $this->getState()->clearIntermediateModel(ChatTip::class);
            $this->getState()->clearIntermediateModel(ChatMember::class);

            return $this->actionTransaction($walletTransaction->id);
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
    public function actionTransactions($id = null, $page = 1, $useState = 0)
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
                        'useState' => $useState,
                    ]),
                    'text' => $transaction->getAmount() . ' ' . $currency->code . ' - ' . Yii::$app->formatter->asDateTime($transaction->getCreatedAtByUser()),
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page, $useState) use ($id) {
                return self::createRoute('transactions', [
                    'id' => $id,
                    'page' => $page,
                    'useState' => $useState,
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
                    'useState' => $useState,
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
    public function actionTransaction($id = null, $useState = 0)
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
                    'timezone' => $this->getGlobalUser()->timezone,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('transactions', [
                                'id' => $walletTransaction->getCurrencyId(),
                                'useState' => $useState,
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
    public function actionDeleteTransaction($walletTransactionId = null, $useState = 0)
    {
        $walletTransaction = WalletTransaction::findOne(['id' => $walletTransactionId]);
        $chatTipWalletTransaction = ChatTipWalletTransaction::findOne(['transaction_id' => $walletTransactionId]);

        if ($walletTransaction && $chatTipWalletTransaction) {
            $transaction = ActiveRecord::getDb()->beginTransaction();
            try {
                $chatTipWalletTransaction->delete();
                $walletTransaction->delete();
                $transaction->commit();
                return $this->actionTransactions($walletTransaction->currency_id, 1, $useState);
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
