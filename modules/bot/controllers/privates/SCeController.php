<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;
use Yii;
use app\modules\bot\components\Controller;

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
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->editMessageTextOrSendMessage(
                    $this->render('index'),
                    [
                        [
                            [
                                'callback_data' => self::createRoute('order'),
                                'text' => 'USD/THB',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('order'),
                                'text' => 'USD/RUB',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('order'),
                                'text' => '❌ ' . 'THB/RUB',
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
                                'text' => Emoji::BACK,
                            ],
                            [
                                'callback_data' => MenuController::createRoute(),
                                'text' => '📱',
                            ],
                            [
                                'callback_data' => self::createRoute('offer'),
                                'text' => '🙋‍♂️ 3',
                            ],
                            [
                                'callback_data' => self::createRoute('order-create'),
                                'text' => Emoji::ADD,
                            ],
                        ],
                    ]
                )
                ->build();
        } else {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->editMessageTextOrSendMessage(
                    $this->render('no-requirements'),
                    [
                        [
                            [
                                'callback_data' => ServicesController::createRoute(),
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

    /**
     * @return array
     */
    public function actionOrderCreate($step = 1)
    {
        //TODO make steps to create a order (maybe in separate actions)

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order-create'),
                [
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
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionOrder()
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order'),
                [
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
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => self::createRoute('order-edit'),
                            'text' => Emoji::EDIT,
                        ],
                        [
                            'callback_data' => self::createRoute('order-remove'),
                            'text' => Emoji::DELETE,
                        ],
                    ],
                ]
            )
            ->build();
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
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order-edit'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('order-selling-currency'),
                            'text' => 'Name',
                        ],
                    ],
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
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
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
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order-selling-rate'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('order'),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionOrderBuyingRate()
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order-selling-rate'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('order'),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionOrderSellingCurrency()
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order-selling-currency'),
                [
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
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionOrderBuyingCurrency()
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order-buying-currency'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('order-selling-currency-payment-methods'),
                            'text' => 'Payment methods',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('order-edit'),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionOffer()
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('offer'),
                [
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
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionOrderSellingCurrencyPaymentMethods()
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order-selling-currency-payment-methods'),
                [
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
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => self::createRoute('order-selling-currency-payment-method-add'),
                            'text' => Emoji::ADD,
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionOrderSellingCurrencyPaymentMethod()
    {
        //TODO save any location that will be sent

        $telegramUser = $this->getTelegramUser();

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->sendLocation(
                $telegramUser->location_lat,
                $telegramUser->location_lon
            )
            ->sendMessage(
                $this->render('order-selling-currency-payment-method'),
                [
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
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => self::createRoute('order-selling-currency-payment-method'),
                            'text' => '🗑',
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionNoRequirements()
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('no-requirements'),
                [
                    [
                        [
                            'callback_data' => ServicesController::createRoute(),
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
