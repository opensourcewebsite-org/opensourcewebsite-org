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
     *
     * @return array
     */
    public function actionIndex($chatTipId = null)
    {
        $chatTip = ChatTip::findOne($chatTipId);

        if (!isset($chatTip)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->actionChooseWallet($chatTipId);
    }

    /**
     * @param int $chatTipId ChatTip->id
     * @param int|null $currencyId Currency->id
     * @param int $page
     *
     * @return array
     */
    public function actionChooseWallet($chatTipId = null, $currencyId = null, $page = 1)
    {
        $chatTip = ChatTip::findOne($chatTipId);

        if (!isset($chatTip)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ($currencyId) {
            $currency = Currency::findOne([
                'id' => $currencyId,
            ]);

            if (!isset($currency)) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery()
                    ->build();
            }

            $walletTransaction = new WalletTransaction([
                'from_user_id' => $this->getTelegramUser()->getUserId(),
                'to_user_id' => $chatTip->toUser->getUserId(),
                'type' => 0,
                'anonymity' => 0,
                'currency_id' => $currency->id,
            ]);

            $this->getState()->setItem($walletTransaction);

            return $this->actionSetAmount($chatTipId);
        }

        $query = $this->getGlobalUser()->getWalletsWithPositiveBalance();

        if (!$query->count()) {
            return $this->getResponseBuilder()
                ->editMessageTextOrSendMessage(
                    $this->render('warning-no-wallets'),
                    [
                        [
                            [
                                'callback_data' => MenuController::createRoute(),
                                'text' => 'OK',
                            ],
                        ],
                    ]
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

        if ($wallets) {
            foreach ($wallets as $wallet) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('choose-wallet', [
                        'chatTipId' => $chatTipId,
                        'currencyId' => $wallet->getCurrencyId(),
                    ]),
                    'text' => $wallet->amount . ' ' . $wallet->currency->code,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($chatTipId) {
                return self::createRoute('choose-wallet', [
                    'chatTipId' => $chatTipId,
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
     *
     * @return array
     */
    public function actionSetAmount($chatTipId = null)
    {
        $chatTip = ChatTip::findOne($chatTipId);

        if (!isset($chatTip)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $walletTransaction = $this->getState()->getItem(WalletTransaction::class);

        if (!isset($walletTransaction)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setInputRoute(self::createRoute('set-amount', [
            'chatTipId' => $chatTipId,
        ]));

        $currency = $walletTransaction->currency;
        $fromUserWallet = $this->getGlobalUser()->getWalletByCurrencyId($currency->id);

        if ($this->getUpdate()->getMessage()) {
            if ($amount = (float)$this->getUpdate()->getMessage()->getText()) {
                $amount = number_format($amount, 2, '.', '');

                if (!$fromUserWallet->hasAmount($amount)) {
                    return $this->getResponseBuilder()
                        ->answerCallbackQuery()
                        ->build();
                }

                if (Number::isFloatLower($amount, WalletTransaction::MIN_AMOUNT)) {
                    $amount = WalletTransaction::MIN_AMOUNT;
                }

                $walletTransaction->amount = $amount;
                $this->getState()->setItem($walletTransaction);

                return $this->actionConfirmTransaction($chatTipId);
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-amount', [
                    'maxAmount' => $fromUserWallet->getAmountMinusFee(),
                    'code' => $currency->code,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('choose-wallet', [
                                'chatTipId' => $chatTipId,
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
     * @param int $chatTipId ChatTip->id
     *
     * @return array
     */
    public function actionConfirmTransaction($chatTipId = null)
    {
        $chatTip = ChatTip::findOne($chatTipId);

        if (!isset($chatTip)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $walletTransaction = $this->getState()->getItem(WalletTransaction::class);

        if (!isset($walletTransaction)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('confirm-transaction', [
                    'walletTransaction' => $walletTransaction,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('send-tip', [
                                'chatTipId' => $chatTipId,
                            ]),
                            'text' => 'CONFIRM',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-amount', [
                                'chatTipId' => $chatTipId,
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
     * @param int $chatTipId ChatTip->id
     *
     * @return array
     */
    public function actionSendTip($chatTipId = null)
    {
        $chatTip = ChatTip::findOne($chatTipId);

        if (!isset($chatTip)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $walletTransaction = $this->getState()->getItem(WalletTransaction::class);

        if (!isset($walletTransaction)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $toUser = $chatTip->toUser;
        $currency = $walletTransaction->currency;
        $walletTransaction->setData(WalletTransaction::CHAT_TIP_ID_DATA_KEY, $chatTip->id);

        if (!$walletTransaction->createTransaction()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $thisChat = $this->getTelegramChat();

        $module = Yii::$app->getModule('bot');
        $module->setChat(Chat::findOne($chatTip->chat_id));

        $response = $module->runAction('tip/tip-message', [
            'chatTipId' => $chatTip->id,
        ]);

        $module->setChat($thisChat);
        $this->getState()->clearItem(WalletTransaction::class);
        $this->getState()->clearInputRoute();

        // send response to private chat
        if ($response) {
            $toUser->sendMessage(
                $this->render('receiver-privates-success', [
                    'walletTransaction' => $walletTransaction,
                    'toUserWallet' => $toUser->getWalletByCurrencyId($currency->id),
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

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }
}
