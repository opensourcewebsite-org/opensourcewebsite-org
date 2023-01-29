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
     *
     * @return array
     */
    public function actionIndex($chatTipId = null)
    {
        $chatTip = ChatTip::findOne($chatTipId);

        if (!isset($chatTip) || !$chatTip->chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setIntermediateFieldArray('WalletTransaction', [
            'from_user_id' => $this->getTelegramUser()->getUserId(),
            'to_user_id' => $chatTip->toUser->getUserId(),
            'type' => 0,
            'anonymity' => 0,
        ]);

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
        $walletTransactionModel = $this->getState()->getIntermediateFieldArray('WalletTransaction');

        if ($currencyId) {
            $currency = Currency::findOne([
                'id' => $currencyId,
            ]);

            if (!isset($currency)) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery()
                    ->build();
            }

            $walletTransactionModel['currency_id'] = $currency->id;
            $this->getState()->setIntermediateFieldArray('WalletTransaction', $walletTransactionModel);

            return $this->actionSetAmount($chatTipId);
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
        $this->getState()->setName(self::createRoute('set-amount', [
            'chatTipId' => $chatTipId,
        ]));

        $walletTransactionModel = $this->getState()->getIntermediateField('WalletTransaction');
        $currency = Currency::findOne($walletTransactionModel['currency_id']);

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

                    $walletTransactionModel['amount'] = $amount;
                    $this->getState()->setIntermediateFieldArray('WalletTransaction', $walletTransactionModel);

                    $toUser = User::findOne($walletTransactionModel['to_user_id']);

                    return $this->getResponseBuilder()
                        ->editMessageTextOrSendMessage(
                            $this->render('confirm-transaction', [
                                'toUser' => $toUser,
                                'amount' => $amount,
                                'code' => $currency->code,
                            ]),
                            [
                                [
                                    [
                                        'callback_data' => self::createRoute('confirm-transaction', [
                                            'chatTipId' => $chatTipId,
                                        ]),
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
                    'maxAmount' => $fromUserWallet->amount - WalletTransaction::FEE,
                    'code' => $currency->code,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'chatTipId' => $chatTipId,
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
     * @param int $chatTipId ChatTip->id
     *
     * @return array
     */
    public function actionConfirmTransaction($chatTipId = null)
    {
        $walletTransactionModel = $this->getState()->getIntermediateField('WalletTransaction');
        $chatTip = ChatTip::findOne($chatTipId);
        $toUser = $chatTip->toUser;
        $currency = Currency::findOne($walletTransactionModel['currency_id']);

        if ($currency) {
            $walletTransaction = new WalletTransaction();
            $walletTransaction->currency_id =  $walletTransactionModel['currency_id'];
            $walletTransaction->from_user_id =  $walletTransactionModel['from_user_id'];
            $walletTransaction->to_user_id =  $walletTransactionModel['to_user_id'];
            $walletTransaction->amount = $walletTransactionModel['amount'];
            $walletTransaction->type = $walletTransactionModel['type'];
            $walletTransaction->anonymity = $walletTransactionModel['anonymity'];

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
                ]);
            }

            $module->setChat($thisChat);
            $this->getState()->setIntermediateFieldArray('WalletTransaction', null);

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
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }
}
