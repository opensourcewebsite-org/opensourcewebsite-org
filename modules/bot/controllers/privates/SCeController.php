<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\components\response\SendMessageCommand;
use app\modules\bot\components\response\SendLocationCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

/**
 * Class SCeController
 *
 * @package app\modules\bot\controllers
 */
class SCeController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
	{
        $telegramUser = $this->getTelegramUser();

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
                                    'callback_data' => self::createRoute('order'),
                                    'text' => 'USD/THB',
                                ],
                                [
                                    'callback_data' => self::createRoute('offer'),
                                    'text' => '🙋‍♂️ 3',
                                ],
                            ],
                            [
                                [
                                    'callback_data' => self::createRoute('order'),
                                    'text' => 'USD/RUB',
                                ],
                                [
                                    'callback_data' => self::createRoute('offer'),
                                    'text' => '🙋‍♂️ 0',
                                ],
                            ],
                            [
                                [
                                    'callback_data' => self::createRoute('order'),
                                    'text' => '❌ ' . 'THB/RUB',
                                ],
                                [
                                    'callback_data' => self::createRoute('offer'),
                                    'text' => '🙋‍♂️ 0',
                                ],
                            ],
                            [
                                [
                                    'callback_data' => self::createRoute('offer'),
                                    'text' => '<',
                                ],
                                [
                                    'callback_data' => self::createRoute('offer'),
                                    'text' => '1/3',
                                ],
                                [
                                    'callback_data' => self::createRoute('offer'),
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
                                    'callback_data' => ServicesController::createRoute(),
                                    'text' => '🔙',
                                ],
                                [
                                    'callback_data' => MenuController::createRoute(),
                                    'text' => '📱',
                                ],
                                [
                                    'callback_data' => self::createRoute('order-create'),
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
                                    'callback_data' => ServicesController::createRoute(),
                                    'text' => '🔙',
                                ],
                                [
                                    'callback_data' => MenuController::createRoute(),
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
     * @return array
     */
    public function actionOrderCreate($step = 1)
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
                                'callback_data' => self::createRoute('order-create'),
                                'text' => 'USD',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('order-create'),
                                'text' => 'THB',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('order-create'),
                                'text' => '<',
                            ],
                            [
                                'callback_data' => self::createRoute('order-create'),
                                'text' => '1/3',
                            ],
                            [
                                'callback_data' => self::createRoute('order-create'),
                                'text' => '>',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute(),
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return array
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
                                'callback_data' => self::createRoute('order-status'),
                                'text' => 'Status: ON',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('offer'),
                                'text' => '🙋‍♂️ 3',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('order-selling-rate'),
                                'text' => 'USD/THB: 30.0000',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('order-buying-rate'),
                                'text' => 'THB/USD: 0.3000',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute(),
                                'text' => '🔙',
                            ],
                            [
                                'callback_data' => MenuController::createRoute(),
                                'text' => '📱',
                            ],
                            [
                                'callback_data' => self::createRoute('order-edit'),
                                'text' => '✏️',
                            ],
                            [
                                'callback_data' => self::createRoute('order-remove'),
                                'text' => '🗑',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return array
     */
    public function actionOrderStatus()
    {
        return $this->actionOrder();
    }

    /**
     * @return array
     */
    public function actionOrderEdit()
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
                                'callback_data' => self::createRoute('order-selling-currency'),
                                'text' => 'USD',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('order-buying-currency'),
                                'text' => 'THB',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('order'),
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return array
     */
    public function actionOrderRemove()
    {
        return $this->actionIndex();
    }

    /**
     * @return array
     */
    public function actionOrderSellingRate()
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
                                'callback_data' => self::createRoute('order'),
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return array
     */
    public function actionOrderBuyingRate()
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
                                'callback_data' => self::createRoute('order'),
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return array
     */
    public function actionOrderSellingCurrency()
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
                                'callback_data' => self::createRoute('order-selling-currency'),
                                'text' => 'Min. amount: ∞',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('order-selling-currency'),
                                'text' => 'Max. amount: 100.00',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('order-selling-currency-payment-methods'),
                                'text' => 'Payment methods',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('order-edit'),
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return array
     */
    public function actionOrderBuyingCurrency()
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
                                'callback_data' => self::createRoute('order-selling-currency-payment-methods'),
                                'text' => 'Payment methods',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('order-edit'),
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return array
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
                                'callback_data' => self::createRoute('offer-like'),
                                'text' => '👍 100',
                            ],
                            [
                                'callback_data' => self::createRoute('offer-like'),
                                'text' => '👎 10',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('offer'),
                                'text' => '<',
                            ],
                            [
                                'callback_data' => self::createRoute('offer'),
                                'text' => '1/3',
                            ],
                            [
                                'callback_data' => self::createRoute('offer'),
                                'text' => '>',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute(),
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return array
     */
    public function actionOrderSellingCurrencyPaymentMethods()
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
                                'callback_data' => self::createRoute('order-selling-currency-payment-method'),
                                'text' => 'Cash',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('order-selling-currency-payment-method'),
                                'text' => 'Online System 1',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('order-selling-currency-payment-method'),
                                'text' => 'Online System 2',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('order-selling-currency-payment-method'),
                                'text' => 'Bank 1',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('order-selling-currency-payment-method'),
                                'text' => 'Bank 2',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('order-selling-currency'),
                                'text' => '🔙',
                            ],
                            [
                                'callback_data' => self::createRoute('order-selling-currency-payment-method-add'),
                                'text' => '➕',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return array
     */
    public function actionOrderSellingCurrencyPaymentMethod()
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
                                'callback_data' => self::createRoute('order-selling-currency-payment-method'),
                                'text' => 'Delivery: ON',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('order-selling-currency-payment-method'),
                                'text' => 'Delivery area: 2 km',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('order-selling-currency-payment-methods'),
                                'text' => '🔙',
                            ],
                            [
                                'callback_data' => self::createRoute('order-selling-currency-payment-method'),
                                'text' => '🗑',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return array
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
                                'callback_data' => ServicesController::createRoute(),
                                'text' => '🔙',
                            ],
                            [
                                'callback_data' => MenuController::createRoute(),
                                'text' => '📱',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }
}
