<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\Controller;
use app\modules\bot\components\FillablePropertiesController;
use app\models\CurrencyExchangeOrder;
use app\models\Currency;
use app\models\User;
use app\models\PaymentMethod;
use app\models\CurrencyExhangeOrderPaymentMethod;
use yii\data\Pagination;
use yii\db\ActiveRecord;
use Yii;

/**
 * Class SCeController
 *
 * @package app\modules\bot\controllers\privates
 */
class SCeController extends FillablePropertiesController
{
    protected static $properties = [
        'selling_currency_min_amount',
        'selling_currency_max_amount',
        'optional_name',
    ];

    /**
     * View of screens of my orders,
     * main screen.
     * view - index
     */
    public function actionIndex($page = 1)
	{
        $telegramUser = $this->getTelegramUser();
        if (($telegramUser->location_lon && $telegramUser->location_lat) && $telegramUser->provider_user_name) {

            $user = $this->getUser();
            $orderCount = $user->getExchangeOrder()->count();
            $pagination = new Pagination([
                'totalCount' => $orderCount,
                'pageSize' => 3,
                'params' => [
                    'page' => $page,
                ],
                'pageSizeParam' => false,
                'validatePage' => true,
            ]);
            $paginationButtons = PaginationButtons::build($pagination, function ($page) {
                return self::createRoute('index', [
                    'page' => $page,
                ]);
            });
            $query = $user->getExchangeOrder();
            $orders = $query
                ->offset($pagination->offset)
                ->limit($pagination->limit)
                ->orderBy(['status' => SORT_DESC, 'selling_currency_id' => SORT_ASC])
                ->all();
            $keyboards = array_map(function ($order) {
                $currency = new Currency();
                $sellingCode = $currency->getCodeById($order->selling_currency_id);
                $buyingCode = $currency->getCodeById($order->buying_currency_id);
                ($order->status == 1 ? $status = '' : $status = '❌ ');

                return [
                    [
                        'text' => $status . $sellingCode . '/' .
                            $buyingCode . ' ' . $order->optional_name,
                        'callback_data' => self::createRoute('order', [
                        'orderId' => $order->id,
                        ]),
                    ],
                ];
            }, $orders);
            $keyboards = array_merge($keyboards, [ $paginationButtons ], [
                [
                    [
                        'text' => Emoji::BACK,
                        'callback_data' => ServicesController::createRoute(),
                    ],
                    [
                        'text' => Emoji::MENU,
                        'callback_data' => MenuController::createRoute(),
                    ],
                    [
                        'text' => '🙋‍♂️ ',
                        'callback_data' => self::createRoute('offer'),
                    ],
                    [
                        'text' => Emoji::ADD,
                        'callback_data' => self::createRoute('order-create', [
                            'page' => 1
                        ]),
                    ],
                ],
            ]);

            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->editMessageTextOrSendMessage(
                    $this->render('index'),
                    $keyboards
                )
                ->build();
        } else {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->editMessageTextOrSendMessage(
                    $this->render('no-requirements'),
                    [
                        [
                            [
                                'text' => Emoji::BACK,
                                'callback_data' => ServicesController::createRoute(),
                            ],
                            [
                                'text' => Emoji::MENU,
                                'callback_data' => MenuController::createRoute(),
                            ],
                        ],
                    ]
                )
                ->build();
        }
    }

    /**
     * Order information viewing screen
     * view - order
     */
    public function actionOrder($orderId)
    {
        $currency = new Currency();
        $order = CurrencyExchangeOrder::findOne($orderId);

        ($order->status == 1 ? $text = 'ON' : $text = 'OFF');
        $status = 'Status: ' . $text;

        $selling = $currency->getCodeById($order->selling_currency_id) . '/' .
                    $currency->getCodeById($order->buying_currency_id) . ': ' .
                    $order->selling_rate;
        $buying = $currency->getCodeById($order->buying_currency_id) . '/' .
                    $currency->getCodeById($order->selling_currency_id) . ': ' .
                    $order->buying_rate;
        $minAmount = number_format($order->selling_currency_min_amount, 2, '.', '');
        $maxAmount = number_format($order->selling_currency_max_amount, 2, '.', '');

        $sellingPaymentMethod = $order->getPaymentMethods(1)->all();
        $buyingPaymentMethod = $order->getPaymentMethods(2)->all();

        $sellingListMethod = array_map(function ($method) {
            return [
                'name' => $method->name,
            ];
        }, $sellingPaymentMethod);
        asort($sellingListMethod);

        $buyingListMethod = array_map(function ($method) {
            return [
                'name' => $method->name,
            ];
        }, $buyingPaymentMethod);
        asort($buyingListMethod);

        $keyboards = [
            [
                [
                    'text' => $status,
                    'callback_data' => self::createRoute('order-status', [
                        'orderId' => $orderId,
                    ]),
                ],
            ],
            [
                [
                    'text' => '🙋‍♂️ ',
                    'callback_data' => self::createRoute('offer'),
                ],
            ],
            [
                [
                    'text' => $selling,
                    'callback_data' => self::createRoute('order-selling-rate', [
                        'orderId' => $orderId,
                    ]),
                ],
            ],
            [
                [
                    'text' => $buying,
                    'callback_data' => self::createRoute('order-buying-rate', [
                        'orderId' => $orderId,
                    ]),
                ],
            ],
            [
                [
                    'text' => Emoji::BACK,
                    'callback_data' => self::createRoute(),
                ],
                [
                    'text' => Emoji::MENU,
                    'callback_data' => MenuController::createRoute(),
                ],
                [
                    'text' => Emoji::EDIT,
                    'callback_data' => self::createRoute('order-edit', [
                        'orderId' => $orderId,
                    ]),
                ],
                [
                    'text' => Emoji::DELETE,
                    'callback_data' => self::createRoute('order-remove', [
                        'orderId' => $orderId,
                    ]),
                ],
            ],
        ];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order', [
                    'selling_currency' => $currency->getCodeById($order->selling_currency_id),
                    'buying_currency' => $currency->getCodeById($order->buying_currency_id),
                    'selling_currency_min_amount' => $minAmount,
                    'selling_currency_max_amount' => $maxAmount,
                    'optional_name' => $order->optional_name,
                    'sellingPaymentMethod' => $sellingListMethod,
                    'buyingPaymentMethod' => $buyingListMethod,
                ]),
                $keyboards
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionOrderCreate($page = 1)
    {
        //TODO make steps to create a order (maybe in separate actions)
        $currencyCount += Currency::find()->count();
        $pagination = new Pagination([
            'totalCount' => $currencyCount,
            'pageSize' => 10,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);
        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('order-create', [
                'page' => $page,
            ]);
        });
        $query = Currency::find();
        $currency = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $keyboards = array_map(function ($currency) {
            return [
                [
                    'text' => $currency->name,
                    'callback_data' => self::createRoute('order-create'),
                ],
            ];
        }, $currency);
        $keyboards = array_merge($keyboards, [ $paginationButtons ], [
            [
                [
                    'text' => Emoji::BACK,
                    'callback_data' => SCeController::createRoute(),
                ],
                [
                    'text' => Emoji::MENU,
                    'callback_data' => MenuController::createRoute(),
                ],
            ],
        ]);


        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order-create'),
                $keyboards
            )
            ->build();
    }

    /**
     * Screen for editing order
     * view - order-edit
     */
    public function actionOrderEdit($orderId)
    {
        $user = $this->getUser();
        $currency = new Currency();

        $order = $user->getExchangeOrder()->where(['id' => $orderId])->one();
        $selling = $currency->getCodeById($order->selling_currency_id);
        $buying = $currency->getCodeById($order->buying_currency_id);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order-edit'),
                [
                    [
                        [
                            'text' => Yii::t('bot', 'Name'),
                            'callback_data' => self::createRoute('optional-name', [
                                'orderId' => $orderId,
                            ]),
                        ],
                    ],
                    [
                        [
                            'text' => $selling,
                            'callback_data' => self::createRoute('order-selling-currency', [
                                'orderId' => $orderId,
                            ]),
                        ],
                    ],
                    [
                        [
                            'text' => $buying,
                            'callback_data' => self::createRoute('order-buying-currency', [
                                'orderId' => $orderId
                            ]),
                        ],
                    ],
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute('order', [
                                'orderId' => $orderId
                            ]),
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * Edit optional name view
     * view - order-optional-name
     */
    public function actionOptionalName($orderId)
    {
        $user = $this->getUser();
        $order = $user->getExchangeOrder()->where(['id' => $orderId])->one();
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order-optional-name', [
                    'optionalName' => $order->optional_name,
                ]),
                [
                    [
                        [
                            'text' => Emoji::EDIT,
                            'callback_data' => self::createRoute('set-property', [
                                'id' => $orderId,
                                'property' => 'optional_name',
                            ]),
                        ],
                        [
                            'text' => Emoji::DELETE,
                            'callback_data' => self::createRoute('optional-name-remove', [
                                'orderId' => $orderId,
                            ]),
                        ],
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute('order-edit', [
                                'orderId' => $orderId
                            ]),
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * Remove optional name and redirect on actionOrder
     */
    public function actionOptionalNameRemove($orderId)
    {
        $order = CurrencyExchangeOrder::findOne($orderId);
        if (isset($order)) {
            $order->optional_name = '';
        }
        $order->save();

        return $this->actionOrder($orderId);
    }

    /**
     * Remove order and redirect on actionIndex
     */
    public function actionOrderRemove($orderId)
    {
        $order = CurrencyExchangeOrder::findOne($orderId);

        if (isset($order)) {
            $order->delete();
        };
        return $this->actionIndex();
    }

    /**
     * Edit order status
     */
    public function actionOrderStatus($orderId)
    {
        $user = $this->getUser();

        $order = $user->getExchangeOrder()->where(['id' => $orderId])->one();
        ($order->status == 1 ? $statusParams = 0 : $statusParams = 1);
        $order->setAttributes([
            'status' => $statusParams,
        ]);
        $order->save();

        return $this->actionOrder($orderId);
    }
    /**
     * Edit order selling rate and redirect on actionIndex
     * view - order-selling-rate
     */
    public function actionOrderSellingRate($orderId)
    {
        $user = $this->getUser();
        $currency = new Currency();

        $this->getState()->setName(self::createRoute('amount-save', [
            'orderId' => $orderId,
            'param' => 'selling_rate',
        ]));

        $order = $user->getExchangeOrder()->where(['id' => $orderId])->one();
        $sellingCode = $currency->getCodeById($order->selling_currency_id);
        $buyingCode = $currency->getCodeById($order->buying_currency_id);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order-selling-rate',[
                    'selling_currency' => $sellingCode,
                    'buying_currency' => $buyingCode,
                    'selling_rate' => $order->selling_rate
                ]),
                [
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute('order', [
                                'orderId' => $orderId,
                            ]),
                        ],
                    ],
                ]
            )
            ->build();
    }
    /**
     * Edit order selling rate and redirect on actionIndex
     * view - order-buying-rate
     */
    public function actionOrderBuyingRate($orderId)
    {
        $user = $this->getUser();
        $currency = new Currency();

        $this->getState()->setName(self::createRoute('amount-save', [
            'orderId' => $orderId,
            'param' => 'buying_rate',
        ]));

        $order = $user->getExchangeOrder()->where(['id' => $orderId])->one();
        $sellingCode = $currency->getCodeById($order->selling_currency_id);
        $buyingCode = $currency->getCodeById($order->buying_currency_id);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order-buying-rate',[
                    'selling_currency' => $sellingCode,
                    'buying_currency' => $buyingCode,
                    'buying_rate' => $order->buying_rate
                ]),
                [
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute('order', [
                                'orderId' => $orderId,
                            ]),
                        ],
                    ],
                ]
            )
            ->build();
    }
    /**
     * Save method for OrderBuyingRate, OrderSellingRate,
     * OrderMinAmount and OrderMaxAmount and redirect on actionOrder
     */
    public function actionAmountSave($orderId, $param)
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        $text = $update->getMessage()->getText();
        $numberFormat = str_replace(',', '.', $text);
        $number = floatval($numberFormat);

        if ($number <= 9999999999) {
            $order = $user->getExchangeOrder()->where(['id' => $orderId])->one();
            switch ($param) {
                case 'max':
                    $number = number_format($number, 2, '.', '');
                    $order->selling_currency_max_amount = $number;
                    break;

                case 'min':
                    $number = number_format($number, 2, '.', '');
                    $order->selling_currency_min_amount = $number;
                    break;

                case 'buying_rate':
                    $number = number_format($number, 8, '.', '');
                    $order->buying_rate = $number;
                    break;

                case 'selling_rate':
                    $number = number_format($number, 8, '.', '');
                    $order->selling_rate = $number;
                    break;
                default:
                    return $this->actionIndex();
            }

            $order->save();

            return $this->actionOrder($orderId);
        }


        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order-amount'),
                [
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute('order-selling-currency', [
                                'orderId' => $orderId,
                            ]),
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * Edit order min amount, redirect actionOrder across actionAmountSave
     * view - order-amount
     */
    public function actionMinAmount($orderId, $param)
    {
        //$update = $this->getUpdate();
        $this->getState()->setName(self::createRoute('amount-save', [
            'orderId' => $orderId,
            'param' => $param,
        ]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order-amount', [
                    'text' => $param,
                ]),
                [
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute('order-selling-currency', [
                                'orderId' => $orderId,
                            ]),
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * Edit order max amount, redirect actionOrder across actionAmountSave
     * view - order-amount
     */
    public function actionMaxAmount($orderId, $param)
    {
        //$update = $this->getUpdate();
        $this->getState()->setName(self::createRoute('amount-save', [
            'orderId' => $orderId,
            'param' => $param,
        ]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order-amount', [
                    'text' => $param,
                ]),
                [
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute('order-selling-currency', [
                                'orderId' => $orderId,
                            ]),
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * Order edit selling curency screen
     * view - order-selling-currency
     */
    public function actionOrderSellingCurrency($orderId)
    {
        $user = $this->getUser();
        $currency = new Currency();

        $order = $user->getExchangeOrder()->where(['id' => $orderId])->one();
        $selling = $currency->getCodeById($order->selling_currency_id);
        $buying = $currency->getCodeById($order->buying_currency_id);
        $minAmount = number_format($order->selling_currency_min_amount, 2, '.', '');
        $maxAmount = number_format($order->selling_currency_max_amount, 2, '.', '');

        $currencyType = 1;

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order-selling-currency',[
                    'selling' => $selling,
                    'buying' => $buying,
                ]),
                [
                    [
                        [
                            'text' => Yii::t('bot', 'Min. amount: ') . $minAmount,
                            'callback_data' => self::createRoute('min-amount', [
                                'orderId' => $orderId,
                                'param' => 'min',
                            ]),
                        ],
                    ],
                    [
                        [
                            'text' => Yii::t('bot', 'Max. amount: ') . $maxAmount,
                            'callback_data' => self::createRoute('max-amount', [
                                'orderId' => $orderId,
                                'param' => 'max',
                            ]),
                        ],
                    ],
                    [
                        [
                            'text' => Yii::t('bot', 'Payment methods'),
                            'callback_data' => self::createRoute('order-currency-payment-methods', [
                                'ordeId' => $orderId,
                                'type' => $currencyType,
                            ]),
                        ],
                    ],
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute('order-edit', [
                                'orderId' => $orderId,
                            ]),
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * Order edit buying curency screen
     * view - order-buying-currency
     */
    public function actionOrderBuyingCurrency($orderId)
    {
        $user = $this->getUser();
        $currency = new Currency();

        $order = $user->getExchangeOrder()->where(['id' => $orderId])->one();
        $selling = $currency->getCodeById($order->selling_currency_id);
        $buying = $currency->getCodeById($order->buying_currency_id);
        $currencyType = 2;

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order-buying-currency',[
                    'selling' => $selling,
                    'buying' => $buying,

                ]),
                [
                    [
                        [
                            'text' => Yii::t('bot', 'Payment methods'),
                            'callback_data' => self::createRoute('order-currency-payment-methods',[
                                'ordeId' => $orderId,
                                'type' => $currencyType,
                            ]),
                        ],
                    ],
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute('order-edit',[
                                'orderId' => $orderId,
                            ]),
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
     * Edit currency payment metods screen
     * view - order-selling-currency-payment-methods
     */
    public function actionOrderCurrencyPaymentMethods($ordeId, $type)
    {
        $currency = new Currency();
        $currencyExchange = CurrencyExchangeOrder::findOne($ordeId);
        $selling = $currency->getCodeById($currencyExchange->selling_currency_id);
        $buying = $currency->getCodeById($currencyExchange->buying_currency_id);
        $paymentMethod = $currencyExchange->getPaymentMethods($type)->all();
        $cashMethod = [];
        foreach ($paymentMethod as $value) {
            $idInt = (int)$value['id'];
            if ($value['name'] == 'Cash') {
                $text = [
                            [
                                'text' => $value['name'],
                                'callback_data' => self::createRoute('order-selling-currency-payment-method', [
                                    'orderId' => $ordeId,
                                    'metodId' => $idInt,
                                ]),
                            ],
                        ];
                array_push($cashMethod, $text);
            }
        }

        $paymentMethodBottons = [];
        foreach ($paymentMethod as $value) {
            $idInt = (int)$value['id'];
            if ($value['name'] !== 'Cash') {
                $text = [
                            [
                                'text' => $value['name'],
                                'callback_data' => self::createRoute('order-selling-currency-payment-method', [
                                    'orderId' => $orderId,
                                    'metodId' => $idInt,
                                ]),
                            ],
                        ];
                array_push($paymentMethodBottons, $text);
            }
        }
        asort($paymentMethodBottons);

        $listMethod = array_map(function ($method){
            return [
                'name' => $method->name,
            ];
        }, $paymentMethod);
        asort($listMethod);

        $keyboards = array_merge($cashMethod, $paymentMethodBottons, [
            [
                [
                    'text' => Emoji::BACK,
                    'callback_data' => self::createRoute('order-edit', [
                        'orderId' => $ordeId,
                    ]),
                ],
                [
                    'text' => Emoji::ADD,
                    'callback_data' => self::createRoute('order-currency-payment-method-add', [
                        'ordeId' => $ordeId,
                        'type' => $type
                    ]),
                ],
            ],
        ]);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order-selling-currency-payment-methods', [
                    'selling' => $selling,
                    'buying' => $buying,
                    'paymentMethod' => $listMethod,
                ]),
                $keyboards
            )
            ->build();
    }

    /**
     * Edit currency payment metod screen
     * view - order-selling-currency-payment-methods
     */
    public function actionOrderSellingCurrencyPaymentMethod($orderId, $metodId)
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
                            'text' => 'Delivery: ON',
                            'callback_data' => self::createRoute('order-selling-currency-payment-method'),
                        ],
                    ],
                    [
                        [
                            'text' => 'Delivery area: 2 km',
                            'callback_data' => self::createRoute('order-selling-currency-payment-method'),
                        ],
                    ],
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute('order-selling-currency-payment-methods',[
                                'orderId' => $orderId,
                            ]),
                        ],
                        [
                            'text' => '🗑',
                            'callback_data' => self::createRoute('order-selling-currency-payment-method-remove',[
                                'orderId' => $orderId,
                            ]),
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * add payment method from currency
     * view - order-selling-currency-payment-method-add
     */
    public function actionOrderCurrencyPaymentMethodAdd($ordeId, $type)
    {
        $user = $this->getUser();
        $currency = new Currency();
        $paymentMethod = new PaymentMethod();
        $methodList = $paymentMethod::find()->all();

        $currencyExchange = CurrencyExchangeOrder::findOne($ordeId);
        $paymentMethod = $currencyExchange->getPaymentMethods($type)->all();

        $arrayMethodList = array_map(function ($method) {
            return $method->id = $method->name;
        }, $methodList);
        $arrayPaymentMethod = array_map(function ($method) {
            return $method->id = $method->name;
        }, $paymentMethod);

        $resultMethodList = array_diff($arrayMethodList, $arrayPaymentMethod);

        asort($resultMethodList);

        $keyboardsPaymentMethod = [];
        foreach ($resultMethodList as $key => $value) {
            $text = [
                        [
                            'text' => $value,
                            'callback_data' => self::createRoute('order-currency-payment-method-save', [
                                'id' => $ordeId,
                                'metod' => $key,
                                'type' => $type,
                            ]),
                        ],
                    ];
            array_push($keyboardsPaymentMethod, $text);
        }

        $keyboards = array_merge($keyboardsPaymentMethod, [
            [
                [
                    'text' => Emoji::BACK,
                    'callback_data' => self::createRoute('order-edit', [
                        'orderId' => $ordeId,
                    ]),
                ],
            ],
        ]);


        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('order-selling-currency-payment-method-add'),
                $keyboards
            )
            ->build();
    }

    /**
     * method save payment method from currency
     */
    public function actionOrderCurrencyPaymentMethodSave($id, $metod, $type)
    {
        $paymentMethod = new CurrencyExhangeOrderPaymentMethod();
        if ($id && $metod) {
            $paymentMethod->order_id = $id;
            $paymentMethod->payment_method_id = $metod;
            $paymentMethod->type = $type;
            $paymentMethod->save();

            return $this->actionIndex();
        }
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

    protected function getModel($id)
    {
        return ($id == null) ? new CurrencyExchangeOrder() : CurrencyExchangeOrder::findOne($id);
    }

    /**
     * @param ActiveRecord $model
     * @param bool $isNew
     * @return array
     */
    protected function afterSave(ActiveRecord $model, bool $isNew)
    {
        return $this->actionOrder($model->id);
    }
}
