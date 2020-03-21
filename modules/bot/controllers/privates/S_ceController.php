<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\SendLocationCommand;
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

        //TODO add this check for all controller actions, remove from actions
        if (($telegramUser->location_lon && $telegramUser->location_lat) && $telegramUser->provider_user_name) {
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
                                    'callback_data' => '/s_ce__order_create',
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
    public function actionOrder_create($step = 1)
    {
        //TODO make steps to create a order (maybe in separate actions)

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('order-create'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/s_ce__order_create',
                                'text' => 'USD',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order_create',
                                'text' => 'THB',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order_create',
                                'text' => '<',
                            ],
                            [
                                'callback_data' => '/s_ce__order_create',
                                'text' => '1/3',
                            ],
                            [
                                'callback_data' => '/s_ce__order_create',
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
                                'callback_data' => '/s_ce__offer',
                                'text' => '🙋‍♂️ 3',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_rate',
                                'text' => 'USD/THB: 30.0000',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order_buying_rate',
                                'text' => 'THB/USD: 0.3000',
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
                                'callback_data' => '/s_ce__order_edit',
                                'text' => '✏️',
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
    public function actionOrder_edit()
    {
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('order-edit'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_currency',
                                'text' => 'USD',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order_buying_currency',
                                'text' => 'THB',
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
                                'callback_data' => '/s_ce__order_edit',
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
                                'callback_data' => '/s_ce__order_edit',
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
                                'callback_data' => '/s_ce__offer_like',
                                'text' => '👍 100',
                            ],
                            [
                                'callback_data' => '/s_ce__offer_like',
                                'text' => '👎 10',
                            ],
                        ],
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
        //TODO save any location that will be sent

        $telegramUser = $this->getTelegramUser();

        return [
            //TODO use location from order
            new SendLocationCommand(
                $this->getTelegramChat()->chat_id,
                $telegramUser->location_lat,
                $telegramUser->location_lon
            ),
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('order-selling-currency-payment-method'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_currency_payment_method',
                                'text' => 'Delivery: ON',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_currency_payment_method',
                                'text' => 'Delivery area: 2 km',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/s_ce__order_selling_currency_payment_methods',
                                'text' => '🔙',
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

    /**
     * @return string
     */
    public function actionNoRequirements()
    {
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
