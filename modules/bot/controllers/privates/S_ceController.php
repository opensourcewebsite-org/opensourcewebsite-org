<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\components\response\SendMessageCommand;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

/**
 * Class DefaultController
 *
 * @package app\modules\bot\controllers
 */
class S_ceController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
	{
        $update = $this->getUpdate();
        $telegramUser = $this->getTelegramUser();
        $user = $this->getUser();

        //TODO PaginationButtons for orders
        if ($tru=1) {
        //if (($telegramUser->location_lon && $telegramUser->location_lat) && $telegramUser->provider_user_name) {
            return [
                new SendMessageCommand(
                    $this->getTelegramChat()->chat_id,
                    $this->render('index'),
                    [
                        'parseMode' => $this->textFormat,
                        'replyMarkup' => new InlineKeyboardMarkup([
                            [
                                [
                                    'callback_data' => '/s_ce__order',
                                    'text' => 'USD/THB',
                                ],
                                [
                                    'callback_data' => '/s_ce__offer',
                                    'text' => '🙋‍♂️ 3',
                                ],
                            ],
                            [
                                [
                                    'callback_data' => '/s_ce__order',
                                    'text' => 'USD/RUB',
                                ],
                                [
                                    'callback_data' => '/s_ce__offer',
                                    'text' => '🙋‍♂️ 0',
                                ],
                            ],
                            [
                                [
                                    'callback_data' => '/s_ce__order',
                                    'text' => '❌ ' . 'THB/RUB',
                                ],
                                [
                                    'callback_data' => '/s_ce__offer',
                                    'text' => '🙋‍♂️ 0',
                                ],
                            ],
                            [
                                [
                                    'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/DONATE.md',
                                    'text' => '👼 ' . Yii::t('bot', 'Donate'),
                                ],
                                [
                                    'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/CONTRIBUTING.md',
                                    'text' => '👨‍🚀 ' . Yii::t('bot', 'Contribution'),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => '/services',
                                    'text' => '🔙',
                                ],
                                [
                                    'callback_data' => '/menu',
                                    'text' => '📱',
                                ],
                                [
                                    'callback_data' => '/s_ce__order_add',
                                    'text' => '➕',
                                ],
                            ],
                        ]),
                    ]
                ),
            ];
        } else {
            return [
                new SendMessageCommand(
                    $this->getTelegramChat()->chat_id,
                    $this->render('no-requirements'),
                    [
                        'parseMode' => $this->textFormat,
                        'replyMarkup' => new InlineKeyboardMarkup([
                            [
                                [
                                    'callback_data' => '/services',
                                    'text' => '🔙',
                                ],
                                [
                                    'callback_data' => '/menu',
                                    'text' => '📱',
                                ],
                            ],
                        ]),
                    ]
                ),
            ];
        }
    }

    /**
     * @return string
     */
    public function actionOrder_add()
    {
        //TODO steps to create a order

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('order-add'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/s_ce',
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return string
     */
    public function actionOrder()
    {
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('order'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/s_ce__order_status',
                                'text' => 'Status: ON',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_rate',
                                'text' => 'USD/THB: 30.0000',
                            ],
                            [
                                'callback_data' => '/s_ce__order_selling_currency',
                                'text' => '✏️',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order_buying_rate',
                                'text' => 'THB/USD: 0.3000',
                            ],
                            [
                                'callback_data' => '/s_ce__order_buying_currency',
                                'text' => '✏️',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce',
                                'text' => '🔙',
                            ],
                            [
                                'callback_data' => '/menu',
                                'text' => '📱',
                            ],
                            [
                                'callback_data' => '/s_ce__order_remove',
                                'text' => '🗑',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return string
     */
    public function actionOrder_status()
    {
        return $this->actionOrder();
    }

    /**
     * @return string
     */
    public function actionOrder_remove()
    {
        return $this->actionIndex();
    }

    /**
     * @return string
     */
    public function actionOrder_selling_rate()
    {
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('order-selling-rate'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/s_ce__order',
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return string
     */
    public function actionOrder_buying_rate()
    {
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('order-selling-rate'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/s_ce__order',
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return string
     */
    public function actionOrder_selling_currency()
    {
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('order-selling-currency'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_currency',
                                'text' => 'Min. amount: ∞',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_currency',
                                'text' => 'Max. amount: 100.00',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_currency_payment_methods',
                                'text' => 'Payment methods',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order',
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return string
     */
    public function actionOrder_buying_currency()
    {
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('order-buying-currency'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_currency_payment_methods',
                                'text' => 'Payment methods',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order',
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return string
     */
    public function actionOffer()
    {
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('offer'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/s_ce__offer',
                                'text' => '<',
                            ],
                            [
                                'callback_data' => '/s_ce__offer',
                                'text' => '1/3',
                            ],
                            [
                                'callback_data' => '/s_ce__offer',
                                'text' => '>',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce',
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return string
     */
    public function actionOrder_selling_currency_payment_methods()
    {
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('order-selling-currency-payment-methods'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_currency_payment_method',
                                'text' => 'Cash',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_currency_payment_method',
                                'text' => 'Online System 1',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_currency_payment_method',
                                'text' => 'Online System 2',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_currency_payment_method',
                                'text' => 'Bank 1',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_currency_payment_method',
                                'text' => 'Bank 2',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_currency',
                                'text' => '🔙',
                            ],
                            [
                                'callback_data' => '/s_ce__order_selling_currency_payment_method_add',
                                'text' => '➕',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return string
     */
    public function actionOrder_selling_currency_payment_method()
    {
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('order-selling-currency-payment-method'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_currency_payment_method',
                                'text' => 'Location',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_currency_payment_method',
                                'text' => 'Delivery: ON',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_currency_payment_method',
                                'text' => 'Delivery zone: 2 km',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_currency_payment_methods',
                                'text' => '🔙',
                            ],
                            [
                                'callback_data' => '/s_ce__order_selling_currency_payment_method',
                                'text' => '✏️',
                            ],
                            [
                                'callback_data' => '/s_ce__order_selling_currency_payment_method',
                                'text' => '🗑',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }
}
