<?php

namespace app\modules\bot\controllers\privates;

use app\helpers\Number;
use app\models\Currency;
use app\models\WalletTransaction;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatTip;
use app\modules\bot\models\User;
use Yii;
use yii\data\Pagination;

/**
 * Class TransactionController
 *
 * @package app\modules\bot\controllers\privates
 */
class TransactionController extends Controller
{
    /**
     * @param int $page
     *
     * @return array
     */
    public function actionIndex($page = 1, $type = WalletTransaction::WALLET_TYPE)
    {
        $this->getState()->clearInputRoute();

        $walletTransaction = $this->getState()->getItem(WalletTransaction::class);

        if (!$walletTransaction->check(WalletTransaction::FROM_USER_CHECK_FLAG)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $walletTransaction->type = $type;
        $this->getState()->setItem($walletTransaction);

        $backRoute = $this->getState()->getBackRoute();

        if (empty($backRoute)) {
            $backRoute = WalletController::createRoute('view', [
                'id' => $walletTransaction->getCurrencyId(),
            ]);
            $this->getState()->setBackRoute($backRoute);
        }

        if ($walletTransaction->type == WalletTransaction::WALLET_TYPE) {
            $wallet = $this->getGlobalUser()->getWalletByCurrencyId($walletTransaction->getCurrencyId());

            if ($wallet->hasAmount()) {
                return $this->actionSetToUser($walletTransaction->getCurrencyId());
            }

            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $query = $this->globalUser->getWalletsWithPositiveBalance()
            ->orderByCurrencyCode();

        if (!$query->count()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery(
                    $this->render('../alert', [
                        'alert' => Yii::t('bot', 'You do not have wallets with positive balance') . '.',
                    ]),
                    true
                )
                ->build();
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

        $wallets = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $buttons = [];

        foreach ($wallets as $wallet) {
            $buttons[][] = [
                'callback_data' => self::createRoute('set-to-user', [
                    'id' => $wallet->getCurrencyId(),
                ]),
                'text' => $wallet->amount . ' ' . $wallet->currency->code,
            ];
        }

        $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($type) {
            return self::createRoute('index', [
                'page' => $page,
                'type' => $type,
            ]);
        });

        if ($paginationButtons) {
            $buttons[] = $paginationButtons;
        }

        $buttons[] = [
            [
                'callback_data' => $backRoute,
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('select-wallet'),
                $buttons
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionSetToUser($id = null)
    {
        $this->getState()->setInputRoute(self::createRoute('set-to-user'));

        $walletTransaction = $this->getState()->getItem(WalletTransaction::class);

        if (!$walletTransaction->check(WalletTransaction::FROM_USER_CHECK_FLAG)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $id = $id ?: $walletTransaction->getCurrencyId();

        $backRoute = $this->getState()->getBackRoute();

        if ($walletTransaction->type != WalletTransaction::WALLET_TYPE) {
            $backRoute = self::createRoute('index', [
                'page' => 1,
                'type' => $walletTransaction->type,
            ]);
        }

        if (empty($backRoute)) {
            $backRoute = WalletController::createRoute('view', [
                'id' => $id,
            ]);
            $this->getState()->setBackRoute($backRoute);
        }

        $currency = Currency::findOne($id);

        if (!$currency) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $walletTransaction->currency_id = $currency->id;
        $this->getState()->setItem($walletTransaction);

        if ($walletTransaction->type != WalletTransaction::WALLET_TYPE && $walletTransaction->check(WalletTransaction::TO_USER_CHECK_FLAG)) {
            return $this->actionInputAmount();
        }

        if ($this->getUpdate()->getMessage()) {
            $text = $this->getUpdate()->getMessage()->getText();

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
            $this->getState()->setItem($walletTransaction);

            $this->getUpdate()->setMessage(null);
            return $this->actionInputAmount();
        }

        $buttons[] = [
            [
                'callback_data' => $backRoute,
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-to-user'),
                $buttons
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionInputAmount()
    {
        $this->getState()->setInputRoute(self::createRoute('input-amount'));

        $walletTransaction = $this->getState()->getItem(WalletTransaction::class);

        if (!$walletTransaction->check(WalletTransaction::FROM_USER_CHECK_FLAG | WalletTransaction::TO_USER_CHECK_FLAG | WalletTransaction::CURRENCY_CHECK_FLAG)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $backRoute = self::createRoute('index', [
            'page' => 1,
            'type' => $walletTransaction->type,
        ]);

        if ($walletTransaction->type == WalletTransaction::WALLET_TYPE) {
            $backRoute = self::createRoute('set-to-user', [
                'id' => $walletTransaction->getCurrencyId(),
            ]);
        }

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
                $this->getState()->setItem($walletTransaction);

                $this->getUpdate()->setMessage(null);
                return $this->actionConfirmation();
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('input-amount', [
                    'walletTransaction' => $walletTransaction,
                ]),
                [
                    [
                        [
                            'callback_data' => $backRoute,
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
    public function actionConfirmation()
    {
        $this->getState()->setInputRoute(self::createRoute('confirmation'));

        $walletTransaction = $this->getState()->getItem(WalletTransaction::class);

        if (!$walletTransaction->check(WalletTransaction::FROM_USER_CHECK_FLAG | WalletTransaction::TO_USER_CHECK_FLAG | WalletTransaction::CURRENCY_CHECK_FLAG | WalletTransaction::AMOUNT_CHECK_FLAG)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $backRoute = self::createRoute('input-amount');

        if ($walletTransaction->type == WalletTransaction::GROUP_MEMBERSHIP_TYPE) {
            $backRoute = $this->getState()->getBackRoute();
        }

        if (empty($backRoute)) {
            $backRoute = WalletController::createRoute('view', [
                'id' => $walletTransaction->getCurrencyId(),
            ]);
            $this->getState()->setBackRoute($backRoute);
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('confirmation', [
                    'walletTransaction' => $walletTransaction,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('confirm-transaction'),
                            'text' => Yii::t('bot', 'CONFIRM'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => $backRoute,
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
        $walletTransaction = $this->getState()->getItem(WalletTransaction::class);

        if (!$walletTransaction->check(WalletTransaction::FROM_USER_CHECK_FLAG | WalletTransaction::TO_USER_CHECK_FLAG | WalletTransaction::CURRENCY_CHECK_FLAG | WalletTransaction::AMOUNT_CHECK_FLAG)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $walletTransactionId = $walletTransaction->createTransaction();

        if ($walletTransactionId) {
            $this->getState()->clearItem(WalletTransaction::class);
            $this->getState()->clearBackRoute();

            $walletTransaction->toUser->botUser->sendMessage(
                $this->render('receiver-privates-success', [
                    'walletTransaction' => $walletTransaction,
                    'toUserWallet' => $walletTransaction->toUser->botUser->getWalletByCurrencyId($walletTransaction->currency->id),
                ]),
                []
            );

            $chatTipId = $walletTransaction->getData(WalletTransaction::CHAT_TIP_ID_DATA_KEY);

            if (isset($chatTipId)) {
                $chatTip = ChatTip::findOne($chatTipId);
            }

            if (isset($chatTip->id)) {
                if (in_array($walletTransaction->type, array(WalletTransaction::GROUP_REPLY_TIP_TYPE, WalletTransaction::GROUP_ANONYMOUS_REPLY_TIP_TYPE))) {
                    $thisChat = $this->chat;

                    $this->module->setChat(Chat::findOne($chatTip->chat_id));

                    $this->module->runAction('tip/tip-message', [
                        'chatTipId' => $chatTip->id,
                    ]);

                    $this->module->setChat($thisChat);
                }
            }

            return $this->run('wallet/transaction', [
                'id' => $walletTransactionId,
            ]);
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }
}
