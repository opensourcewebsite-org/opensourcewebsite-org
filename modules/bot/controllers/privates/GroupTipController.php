<?php

namespace app\modules\bot\controllers\privates;

use app\models\Currency;
use app\models\DebtBalance;
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
     *
     * @return array
     */
    public function actionView($chatId = null, $toUserId = null)
    {
        $fromUser = $this->getTelegramUser();
        $chat = Chat::findOne($chatId);
        $toUser = User::findOne($toUserId);

        if (!isset($chat) || !$chat->isGroup() || !isset($toUser)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

//        $this->getState()->setName(self::createRoute('choose-currency', [
//            'chatId' => $chatId,
//            'toUserId' => $toUserId,
//        ]));

        $this->getState()->setName(json_encode([
            'chatId' => $chatId,
            'toUserId' => $toUserId,
        ]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('view', [
                    'fromUser' => $fromUser,
                    'toUser' => $toUser,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('choose-currency'),
                            'text' =>'Currency',
                        ],
                    ],
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

    /**
     * @param int $chatId Chat->id
     * @param int $toUserId User->id
     * @param string $code Currency->code
     *
     * @return array
     */
    public function actionSetAmount($chatId = null, $toUserId = null, $code = null)
    {
        $this->getState()->setName(self::createRoute('set-amount', [
            'chatId' => $chatId,
            'toUserId' => $toUserId,
            'code' =>$code,
        ]));

        if ($this->getUpdate()->getMessage()) {
            if ((float)$this->getUpdate()->getMessage()->getText()) {
                $amount = (float)$this->getUpdate()->getMessage()->getText();
                $amount = number_format($amount, 2, '.', '');

                $this->getState()->setName(json_encode([
                    'chatId' => $chatId,
                    'toUserId' => $toUserId,
                    'code' => $code,
                    'amount' => $amount,
                ]));

                $fromUser = $this->getTelegramUser();
                $toUser = User::findOne($toUserId);

                return $this->getResponseBuilder()
                    ->editMessageTextOrSendMessage(
                        $this->render('confirm-transaction', [
                            'fromUser' => $fromUser,
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
                                    'callback_data' => self::createRoute('view', [
                                        'chatId' => $chatId,
                                        'toUserId' => $toUserId,
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
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-amount'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('view', [
                                'chatId' => $chatId,
                                'toUserId' => $toUserId,
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
     * @param string|null $code Currency->code
     * @param int $page
     *
     * @return array
     */
    public function actionChooseCurrency($code = null, $page = 1)
    {
        $state = json_decode($this->getState()->getName());
        $fromUser = $this->getTelegramUser();
        $toUser = User::findOne($state->toUserId);

        if ($code) {
            $currency = Currency::findOne([
                'code' => $code,
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

                if (!$fromUserWallet || !$toUserWallet) {
                    return $this->getResponseBuilder()
                        ->answerCallbackQuery()
                        ->build();
                }

                return $this->actionSetAmount($state->chatId, $state->toUserId, $code);
            }
        }

        $query = Currency::find()
            ->joinWith('wallets')
            ->andWhere(['>', Wallet::tableName() . '.amount', 0])
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
                    'callback_data' => self::createRoute('choose-currency', [
                        'code' => $currency->code,
                    ]),
                    'text' => $currency->code . ' - ' . $currency->name,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) {
                return self::createRoute('choose-currency', [
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
                    'chatId' => $state->chatId,
                    'toUserId' => $state->toUserId,
                ]),
                'text' => Emoji::DELETE,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('choose-currency'),
                $buttons
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

            if (($fromUserWallet->amount - $state->amount - WalletTransaction::TRANSACTION_FEE) < 0) {
                return $this->getResponseBuilder()
                    ->editMessageTextOrSendMessage(
                        $this->render('warning-confirm-transaction'),
                        [
                            [
                                [
                                    'callback_data' => self::createRoute('view', [
                                        'chatId' => $state->chatId,
                                        'toUserId' => $state->toUserId,
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

                // send group message
                $thisChat = $this->getTelegramChat();
                $module = Yii::$app->getModule('bot');
                $module->setChat(Chat::findOne($state->chatId));
                $response = $module->runAction('tip/show-tip-message', [
                    'chatId' => $state->chatId,
                    'walletTransaction' => $walletTransaction,
                ]);

                $module->setChat($thisChat);

                // send response to private chat
                if ($response) {
                    return $this->getResponseBuilder()
                        ->editMessageTextOrSendMessage(
                            $this->render('success'),
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
