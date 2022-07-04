<?php

namespace app\modules\bot\controllers\privates;

use app\behaviors\SetAttributeValueBehavior;
use app\behaviors\SetDefaultCurrencyBehavior;
use app\models\Currency;
use app\models\CurrencyExchangeOrder;
use app\models\CurrencyExchangeOrderBuyingPaymentMethod;
use app\models\CurrencyExchangeOrderMatch;
use app\models\CurrencyExchangeOrderSellingPaymentMethod;
use app\models\PaymentMethod;
use app\models\scenarios\CurrencyExchangeOrder\SetActiveScenario;
use app\models\User;
use app\modules\bot\components\crud\CrudController;
use app\modules\bot\components\crud\rules\ExplodeStringFieldComponent;
use app\modules\bot\components\crud\rules\LocationToArrayFieldComponent;
use app\modules\bot\components\crud\services\IntermediateFieldService;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\validators\RadiusValidator;
use Yii;
use yii\data\Pagination;
use yii\db\ActiveRecord;

/**
 * Class CeController
 *
 * @link https://opensourcewebsite.org/currency-exchange-order
 * @package app\modules\bot\controllers\privates
 */
class CeController extends CrudController
{
    protected $updateAttributes = [
        'sellingCurrency',
        'buyingCurrency',
        'location',
        'delivery_radius',
    ];

    /**
     * {@inheritdoc}
     */
    protected function rules()
    {
        return [
            'model' => CurrencyExchangeOrder::class,
            'prepareViewParams' => function ($params) {
                /** @var Vacancy $model */
                $model = $params['model'] ?? null;

                return [
                    'model' => $model,
                    'locationLink' => ExternalLink::getOSMLink($model->location_lat, $model->location_lon),
                    'sellingPaymentMethods' => array_map(function ($paymentMethod) {
                        return $paymentMethod->getLabel();
                    }, $model->sellingPaymentMethods),
                    'buyingPaymentMethods' => array_map(function ($paymentMethod) {
                        return $paymentMethod->getLabel();
                    }, $model->buyingPaymentMethods),
                ];
            },
            'create' => [
                'sellingCurrency',
                'buyingCurrency',
                'fee',
                'selling_currency_min_amount',
                'selling_currency_max_amount',
                'selling_cash_on',
                'sellingPaymentMethods',
                'buying_cash_on',
                'buyingPaymentMethods',
                'location',
                'delivery_radius',
                'user_id',
            ],
            'edit' => [
                [
                    'text' => function (CurrencyExchangeOrder $model) {
                        return $model->sellingCurrency->code;
                    },
                    'buttons' => [
                        'selling_currency_min_amount',
                        'selling_currency_max_amount',
                        'selling_cash_on',
                        'sellingPaymentMethods',
                    ],
                ],
                [
                    'text' => function (CurrencyExchangeOrder $model) {
                        return $model->buyingCurrency->code;
                    },
                    'buttons' => [
                        'buying_cash_on',
                        'buyingPaymentMethods',
                    ],
                ],
                'location' => [
                    //'hideCondition' => !$this->getTelegramUser()->location_lat || !$this->getTelegramUser()->location_lon,
                ],
                'delivery_radius' => [
                    //'hideCondition' => !$this->getTelegramUser()->location_lat || !$this->getTelegramUser()->location_lon,
                ],
            ],
            'attributes' => [
                'sellingCurrency' => [
                    'view' => 'set-selling_currency',
                    'relation' => [
                        'attributes' => [
                            'selling_currency_id' => [Currency::class, 'id', 'code'],
                        ],
                    ],
                ],
                'buyingCurrency' => [
                    'view' => 'set-buying_currency',
                    'relation' => [
                        'attributes' => [
                            'buying_currency_id' => [Currency::class, 'id', 'code'],
                        ],
                    ],
                ],
                'fee' => [
                ],
                'selling_currency_min_amount' => [
                    'isRequired' => false,
                ],
                'selling_currency_max_amount' => [
                    'isRequired' => false,
                ],
                'selling_cash_on' => [
                    'isRequired' => false,
                    'buttons' => [
                        [
                            'text' => Yii::t('bot', 'YES'),
                            'callback' => function (CurrencyExchangeOrder $model) {
                                $model->selling_cash_on = CurrencyExchangeOrder::CASH_ON;

                                return $model;
                            },
                        ],
                        [
                            'text' => Yii::t('bot', 'NO'),
                            'callback' => function (CurrencyExchangeOrder $model) {
                                $model->selling_cash_on = CurrencyExchangeOrder::CASH_OFF;

                                return $model;
                            },
                        ],
                    ],
                ],
                'sellingPaymentMethods' => [
                    'view' => 'set-selling_payment_methods',
                    'samePageAfterAdd' => true,
                    'enableAddButton' => true,
                    'showRowsList' => true,
                    'createRelationIfEmpty' => true,
                    'relation' => [
                        'model' => CurrencyExchangeOrderSellingPaymentMethod::class,
                        'attributes' => [
                            'order_id' => [CurrencyExchangeOrder::class, 'id'],
                            'payment_method_id' => [PaymentMethod::class, 'id', 'name', 'type'],
                        ],
                        'removeOldRows' => true,
                    ],
                    'buttons' => [
                        [
                            'text' => Yii::t('bot', 'NEXT'),
                            'callback' => function (CurrencyExchangeOrder $model) {
                                return $model;
                            },
                        ],
                    ],
                ],
                'buying_cash_on' => [
                    'isRequired' => false,
                    'buttons' => [
                        [
                            'text' => Yii::t('bot', 'YES'),
                            'callback' => function (CurrencyExchangeOrder $model) {
                                $model->buying_cash_on = CurrencyExchangeOrder::CASH_ON;

                                return $model;
                            },
                        ],
                        [
                            'text' => Yii::t('bot', 'NO'),
                            'callback' => function (CurrencyExchangeOrder $model) {
                                $model->buying_cash_on = CurrencyExchangeOrder::CASH_OFF;

                                return $model;
                            },
                        ],
                    ],
                ],
                'buyingPaymentMethods' => [
                    'view' => 'set-buying_payment_methods',
                    'samePageAfterAdd' => true,
                    'enableAddButton' => true,
                    'showRowsList' => true,
                    'createRelationIfEmpty' => true,
                    'relation' => [
                        'model' => CurrencyExchangeOrderBuyingPaymentMethod::class,
                        'attributes' => [
                            'order_id' => [CurrencyExchangeOrder::class, 'id'],
                            'payment_method_id' => [PaymentMethod::class, 'id', 'name', 'type'],
                        ],
                        'removeOldRows' => true,
                    ],
                    'buttons' => [
                        [
                            'text' => Yii::t('bot', 'NEXT'),
                            'callback' => function (CurrencyExchangeOrder $model) {
                                return $model;
                            },
                        ],
                    ],
                ],
                'location' => [
                    'isRequired' => false,
                    'component' => LocationToArrayFieldComponent::class,
                    'buttons' => [
                        [
                            //'hideCondition' => !$this->getTelegramUser()->location_lat || !$this->getTelegramUser()->location_lon,
                            'text' => Yii::t('bot', 'MY LOCATION'),
                            'callback' => function (CurrencyExchangeOrder $model) {
                                $latitude = 0;//$this->getTelegramUser()->location_lat;
                                $longitude = 0;//$this->getTelegramUser()->location_lon;
                                if ($latitude && $longitude) {
                                    $model->location_lat = $latitude;
                                    $model->location_lon = $longitude;

                                    return $model;
                                }

                                return null;
                            },
                        ],
                    ],
                ],
                'delivery_radius' => [
                    'buttons' => [
                        [
                            'text' => Yii::t('bot', 'NO'),
                            'callback' => function (CurrencyExchangeOrder $model) {
                                $model->delivery_radius = 0;

                                return $model;
                            },
                        ],
                    ],
                ],
                'cross_rate_on' => [
                    'isRequired' => false,
                    'hidden' => true,
                ],
                'user_id' => [
                    'behaviors' => [
                        'SetAttributeValueBehavior' => [
                            'class' => SetAttributeValueBehavior::class,
                            'attributes' => [
                                ActiveRecord::EVENT_BEFORE_VALIDATE => ['user_id'],
                                ActiveRecord::EVENT_BEFORE_INSERT => ['user_id'],
                            ],
                            'attribute' => 'user_id',
                            'value' => $this->module->user->id,
                        ],
                    ],
                    'hidden' => true,
                ],
            ],
        ];
    }

    /**
     * @param int $page
     *
     * @return array
     */
    public function actionIndex($page = 1)
    {
        $this->getState()->setName(null);

        $globalUser = $this->getUser();

        $query = CurrencyExchangeOrder::find()
            ->userOwner()
            ->orderBy([
                'status' => SORT_DESC,
                'selling_currency_id' => SORT_ASC,
                'buying_currency_id' => SORT_ASC,
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

        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('index', [
                'page' => $page,
            ]);
        });

        $buttons = [];

        $orders = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($orders) {
            foreach ($orders as $order) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('view', [
                        'id' => $order->id,
                    ]),
                    'text' => ($order->isActive() ? '' : Emoji::INACTIVE . ' ') . '#' . $order->id . ' ' . $order->getTitle(),
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $rowButtons[] = [
            'callback_data' => MenuController::createRoute(),
            'text' => Emoji::MENU,
        ];

        $matchesCount = CurrencyExchangeOrderMatch::find()
            ->joinWith('order')
            ->andWhere([
                CurrencyExchangeOrder::tableName() . '.user_id' => $globalUser->id,
            ])
            ->count();

        if ($matchesCount) {
            $rowButtons[] = [
                'callback_data' => self::createRoute('all-matches'),
                'text' => Emoji::OFFERS . ' ' . $matchesCount,
                'visible' => YII_ENV_DEV,
            ];
        }

        $rowButtons[] = [
            'callback_data' => self::createRoute('create'),
            'text' => Emoji::ADD,
            'visible' => YII_ENV_DEV,
        ];

        $buttons[] = $rowButtons;

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                $buttons
            )
            ->build();
    }

    /**
     * @param int $id CurrencyExchangeOrder->id
     *
     * @return array
     */
    public function actionView($id = null)
    {
        $order = CurrencyExchangeOrder::find()
            ->where([
                'id' => $id,
            ])
            ->userOwner()
            ->one();

        if (!isset($order)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $buttons[] = [
            [
                'callback_data' => self::createRoute('set-status', [
                    'id' => $order->id,
                ]),
                'text' => $order->isActive() ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
            ],
        ];

        $matchesCount = $order->getMatches()->count();

        if ($matchesCount) {
            $buttons[][] = [
                'callback_data' => self::createRoute('matches', [
                    'id' => $order->id,
                ]),
                'text' => Emoji::OFFERS . ' ' . $matchesCount,
            ];
        }

        // $buttons[] = [
        //     [
        //         'text' => $order->getTitle() . ': ' . ($order->cross_rate_on ? Yii::t('bot', 'Cross rate') : (float)$order->selling_rate),
        //         'callback_data' => self::createRoute('e-a', [
        //             'id' => $order->id,
        //             'a' => 'selling_rate',
        //         ]),
        //     ],
        // ];
        //
        // $buttons[] = [
        //     [
        //         'text' => $order->getInverseTitle() . ': ' . ($order->cross_rate_on ? Yii::t('bot', 'Cross rate') : (float)$order->buying_rate),
        //         'callback_data' => self::createRoute('e-a', [
        //             'id' => $order->id,
        //             'a' => 'buying_rate',
        //         ]),
        //     ],
        // ];

        $buttons[] = [
            [
                'callback_data' => self::createRoute('index'),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'callback_data' => self::createRoute('update', [
                    'id' => $order->id,
                ]),
                'text' => Emoji::EDIT,
                'visible' => YII_ENV_DEV,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('view', [
                    'model' => $order,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionOrderCreate($step = 1)
    {
        //TODO make steps to create a order (maybe in separate actions)
        return $this->getResponseBuilder()
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
    public function actionOrderEdit()
    {
        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('order-edit'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('order-selling-currency'),
                            'text' => 'USD',
                        ],
                        [
                            'callback_data' => self::createRoute('order-buying-currency'),
                            'text' => 'THB',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('order-selling-currency'),
                            'text' => 'Name',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('order-location'),
                            'text' => 'Location',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('order-delivery-radius'),
                            'text' => 'Delivery radius',
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
        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('order-selling-rate'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('order-selling-rate'),
                            'text' => Yii::t('bot', 'Cross rate'),
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
    public function actionOrderBuyingRate()
    {
        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('order-selling-rate'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('order-buying-rate'),
                            'text' => Yii::t('bot', 'Cross rate'),
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
    public function actionOrderSellingCurrency()
    {
        return $this->getResponseBuilder()
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
        return $this->getResponseBuilder()
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
    public function actionOrderSellingCurrencyPaymentMethods()
    {
        return $this->getResponseBuilder()
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
        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('order-selling-currency-payment-method'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('order-selling-currency-payment-methods'),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => self::createRoute('order-selling-currency-payment-method'),
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
    // public function actionOrderLocation()
    // {
    //     //TODO save any location that will be sent
    //     $telegramUser = $this->getTelegramUser();
    //
    //     if ($telegramUser->location_lat && $telegramUser->location_lon) {
    //         return $this->getResponseBuilder()
    //             ->sendLocation(
    //                 $telegramUser->location_lat,
    //                 $telegramUser->location_lon
    //             )
    //             ->editMessageTextOrSendMessage(
    //                 $this->render('order-location'),
    //                 [
    //                     [
    //                         [
    //                             'callback_data' => self::createRoute('order-edit'),
    //                             'text' => Emoji::BACK,
    //                         ],
    //                     ],
    //                 ]
    //             )
    //             ->build();
    //     }
    // }

    /**
     * @return array
     */
    public function actionOrderDeliveryRadius()
    {
        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('order-delivery-radius'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('order-edit'),
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
     * @param int $page
     * @param int $id CurrencyExchangeOrder->id
     *
     * @return array
     */
    public function actionMatches($page = 1, $id = null)
    {
        $globalUser = $this->getUser();

        $order = $globalUser->getCurrencyExchangeOrders()
            ->where([
                'id' => $id,
            ])
            ->one();

        if (!isset($order)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $query = $order->getMatchesOrderByRank();
        $matchesCount = $query->count();

        if (!$matchesCount) {
            return $this->actionView($order->id);
        }

        $pagination = new Pagination([
            'totalCount' => $matchesCount,
            'pageSize' => 1,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $matchOrder = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->one();

        if (!$matchOrder) {
            return $this->actionView($order->id);
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'id' => $order->id,
                ]),
                'text' => '#' . $order->id . ' ' . $order->getTitle(),
            ]
        ];

        $buttons[] = PaginationButtons::build($pagination, function ($page) use ($order) {
            return self::createRoute('matches', [
                'id' => $order->id,
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'id' => $order->id,
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
                $this->render('match', [
                    'model' => $matchOrder,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @param int $page
     *
     * @return array
     */
    public function actionAllMatches($page = 1)
    {
        $user = $this->getUser();

        $query = CurrencyExchangeOrderMatch::find()
            ->joinWith('order')
            ->andWhere([
                CurrencyExchangeOrder::tableName() . '.user_id' => $user->id,
            ]);

        $matchesCount = $query->count();

        if (!$matchesCount) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $pagination = new Pagination([
            'totalCount' => $matchesCount,
            'pageSize' => 1,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $mathes = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->one();

        $order = $currencyExchangeOrderMatch->order;
        $matchOrder = $currencyExchangeOrderMatch->matchOrder;

        $buttons[] = [
            [
                'text' => $order->getTitle(),
                'callback_data' => self::createRoute('view', [
                    'id' => $order->id,
                ]),
            ]
        ];

        $buttons[] = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('matches', [
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'callback_data' => self::createRoute('index'),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('match', [
                    'model' => $matchOrder,
                    'user' => TelegramUser::findOne(['user_id' => $matchOrder->user_id]),
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @param int $id
     */
    public function actionDelete($id)
    {
        $user = $this->getUser();

        $order = $user->getCurrencyExchangeOrders()
            ->where([
                'user_id' => $user->id,
                'id' => $id,
            ])
            ->one();

        if (!isset($order)) {
            return [];
        }

        $order->delete();

        return $this->actionIndex();
    }

    /**
     * @param int $id CurrencyExchangeOrder->id
     *
     * @return array
     */
    public function actionSetStatus($id = null)
    {
        $model = CurrencyExchangeOrder::find()
            ->where([
                'id' => $id,
            ])
            ->userOwner()
            ->one();

        if (!isset($model)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        switch ($model->status) {
            case CurrencyExchangeOrder::STATUS_ON:
                $model->setInactive();
                $model->save(false);

                break;
            case CurrencyExchangeOrder::STATUS_OFF:
                $scenario = new SetActiveScenario($model);

                if ($scenario->run()) {
                    $model->save(false);
                } else {
                    return $this->getResponseBuilder()
                        ->answerCallbackQuery(
                            $this->render('../alert', [
                                'alert' => $scenario->getFirstError(),
                            ]),
                            true
                        )
                        ->build();
                }
        }

        return $this->actionView($model->id);
    }
}
