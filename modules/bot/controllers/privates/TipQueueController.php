<?php

namespace app\modules\bot\controllers\privates;

use app\helpers\Number;
use app\models\Currency;
use app\models\traits\FloatAttributeTrait;
use app\models\WalletTransaction;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatTip;
use app\modules\bot\models\ChatTipQueue;
use app\modules\bot\models\User;
use Yii;
use yii\data\Pagination;

/**
 * Class TipQueueController
 *
 * @package app\modules\bot\controllers\privates
 */
class TipQueueController extends Controller
{
    public function actionIndex($chatId = null)
    {
        $this->getState()->clearInputRoute();

        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'chat' => $chat,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('select-wallet', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'CONTINUE'),
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
     * @param int $page
     *
     * @return array
     */
    public function actionSelectWallet($page = 1, $chatId = null)
    {
        $this->getState()->setInputRoute(self::createRoute('select-wallet', [
            'page' => $page,
            'chatId' => $chatId,
        ]));

        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatTipQueue = $this->getState()->getItem(ChatTipQueue::class);
        $chatTipQueue->user_id = $this->getTelegramUser()->getId();
        $chatTipQueue->chat_id = $chat->id;
        $this->getState()->setItem($chatTipQueue);

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
                'callback_data' => self::createRoute('set-user-count', [
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

        $buttons[] = [
            [
                'callback_data' => self::createRoute('index', [
                    'page' => 1,
                    'chatId' => $chatId,
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
                $this->render('select-wallet'),
                $buttons
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionSetUserCount($id = null)
    {
        $this->getState()->setInputRoute(self::createRoute('set-user-count', [
            'id' => $id,
        ]));

        $currency = Currency::findOne($id);

        if (!$currency) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatTipQueue = $this->getState()->getItem(ChatTipQueue::class);

        if (!$chatTipQueue->check()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatTipQueue->currency_id = $currency->id;
        $this->getState()->setItem($chatTipQueue);

        if ($this->getUpdate()->getMessage()) {
            $count = (int)$this->getUpdate()->getMessage()->getText();

            if ($count < ChatTipQueue::USER_MIN_COUNT || $count > ChatTipQueue::USER_MAX_COUNT) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery()
                    ->build();
            }

            $chatTipQueue->user_count = $count;
            $this->getState()->setItem($chatTipQueue);

            $this->getUpdate()->setMessage(null);
            return $this->actionSetUserAmount();
        }

        $chatId = $chatTipQueue->getChatId();

        $buttons[] = [
            [
                'callback_data' => self::createRoute('select-wallet', [
                    'chatId' => $chatId,
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
                $this->render('set-user-count'),
                $buttons
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionSetUserAmount()
    {
        $this->getState()->setInputRoute(self::createRoute('set-user-amount'));

        $chatTipQueue = $this->getState()->getItem(ChatTipQueue::class);

        if (!$chatTipQueue->check(ChatTipQueue::USER_CHECK_FLAG | ChatTipQueue::CHAT_CHECK_FLAG | ChatTipQueue::CURRENCY_CHECK_FLAG | ChatTipQueue::USER_COUNT_CHECK_FLAG)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ($this->getUpdate()->getMessage()) {
            if ($amount = (float)$this->getUpdate()->getMessage()->getText()) {
                $amount = number_format($amount, 2, '.', '');

                if (!$this->getGlobalUser()->getWalletByCurrencyId($chatTipQueue->currency_id)->hasAmount($amount)) {
                    return $this->getResponseBuilder()
                        ->answerCallbackQuery()
                        ->build();
                }

                if (Number::isFloatLower($amount, ChatTipQueue::USER_MIN_AMOUNT)) {
                    $amount = ChatTipQueue::USER_MIN_AMOUNT;
                }

                $chatTipQueue->user_amount = $amount;
                $this->getState()->setItem($chatTipQueue);

                $this->getUpdate()->setMessage(null);
                return $this->actionConfirmation();
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-user-amount', [
                    'chatTipQueue' => $chatTipQueue,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-user-count', [
                                'id' => $chatTipQueue->getCurrencyId(),
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
    public function actionConfirmation()
    {
        $this->getState()->setInputRoute(self::createRoute('confirmation'));

        $chatTipQueue = $this->getState()->getItem(ChatTipQueue::class);

        if (!$chatTipQueue->check(ChatTipQueue::USER_CHECK_FLAG | ChatTipQueue::CHAT_CHECK_FLAG | ChatTipQueue::CURRENCY_CHECK_FLAG | ChatTipQueue::USER_COUNT_CHECK_FLAG | ChatTipQueue::USER_AMOUNT_CHECK_FLAG)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('confirmation', [
                    'chatTipQueue' => $chatTipQueue,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('create-queue'),
                            'text' => Yii::t('bot', 'CONFIRM'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-user-amount'),
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
    public function actionCreateQueue()
    {
        $chatTipQueue = $this->getState()->getItem(ChatTipQueue::class);

        if (!$chatTipQueue->check(ChatTipQueue::USER_CHECK_FLAG | ChatTipQueue::CHAT_CHECK_FLAG | ChatTipQueue::CURRENCY_CHECK_FLAG | ChatTipQueue::USER_COUNT_CHECK_FLAG | ChatTipQueue::USER_AMOUNT_CHECK_FLAG)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatTipQueue->save();

        if (!isset($chatTipQueue->id)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->clearItem(ChatTipQueue::class);

        $thisChat = $this->chat;
        $module = Yii::$app->getModule('bot');
        $module->setChat(Chat::findOne($chatTipQueue->chat_id));

        $response = $module->runAction('tip-queue/tip-message', [
            'queueId' => $chatTipQueue->id,
        ]);

        $module->setChat($thisChat);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('success', [
                    'chatTipQueue' => $chatTipQueue,
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
