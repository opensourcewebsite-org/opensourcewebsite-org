<?php

namespace app\modules\bot\controllers\privates;

use app\models\Currency;
use app\models\Wallet;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use Yii;
use yii\data\Pagination;

/**
 * Class WalletController
 *
 * @package app\modules\bot\controllers\privates
 */
class WalletController extends Controller
{
    /**
     * @param int $page
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
     * @return array
     */
    public function actionSendTransaction($id = null)
    {
        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param int $id Currency->id
     * @return array
     */
    public function actionTransactions($id = null)
    {
        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }
}
