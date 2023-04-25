<?php

namespace app\modules\bot\controllers\privates;

use app\behaviors\SetAttributeValueBehavior;
use app\behaviors\SetDefaultCurrencyBehavior;
use app\models\Currency;
use app\models\CurrencyExchangeOrder;
use app\models\CurrencyExchangeOrderBuyingPaymentMethod;
use app\models\CurrencyExchangeOrderMatch;
use app\models\CurrencyExchangeOrderSellingPaymentMethod;
use app\models\events\interfaces\ViewedByUserInterface;
use app\models\PaymentMethodCurrencyByCurrency;
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
        'selling_currency_edit',
        'buying_currency_edit',
    ];

    /**
     * {@inheritdoc}
     */
    protected function rules()
    {
        return [
            'model' => CurrencyExchangeOrder::class,
            'prepareViewParams' => function ($params) {
                /** @var CurrencyExchangeOrder $model */
                $model = $params['model'] ?? null;

                return [
                    'model' => $model,
                    /*'sellingPaymentMethods' => array_map(function ($paymentMethod) {
                        return $paymentMethod->getLabel();
                    }, $model->sellingPaymentMethods),
                    'buyingPaymentMethods' => array_map(function ($paymentMethod) {
                        return $paymentMethod->getLabel();
                    }, $model->buyingPaymentMethods),*/
                ];
            },
            'attributes' => [
                'sellingCurrency' => [
                    'view' => 'set-selling_currency',
                    'systemButtons' => [
                        [
                            [
                                'editMode' => false,
                                'route' => $this->backRoute->get(),
                                'text' => Emoji::BACK,
                            ],
                        ],
                    ],
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
                'selling_rate' => [
                    'isRequired' => false,
                    'buttons' => [
                        [
                            [
                                'hideCondition' => function () {
                                    $selling_rate = $this->field->get($this->modelName, 'selling_rate');
                                    return !isset($selling_rate);
                                },
                                'text' => Yii::t('bot', 'NO'),
                                'callback' => function (CurrencyExchangeOrder $model) {
                                    $model->selling_rate = null;

                                    return $model;
                                },
                            ],
                        ],
                    ],
                ],
                'buying_rate' => [
                    'isRequired' => false,
                    'buttons' => [
                        [
                            [
                                'hideCondition' => function () {
                                    $buying_rate = $this->field->get($this->modelName, 'buying_rate');
                                    return !isset($buying_rate);
                                },
                                'text' => Yii::t('bot', 'NO'),
                                'callback' => function (CurrencyExchangeOrder $model) {
                                    $model->buying_rate = null;

                                    return $model;
                                },
                            ],
                        ],
                    ],
                    'hidden' => function () {
                        $selling_rate = $this->field->get($this->modelName, 'selling_rate');
                        return isset($selling_rate);
                    },
                ],
                'selling_currency_min_amount' => [
                    'isRequired' => false,
                ],
                'selling_currency_max_amount' => [
                    'isRequired' => false,
                ],
                'sellingPaymentMethods' => [
                    'isRequired' => false,
                    'view' => 'choose-selling_payment_methods',
                    'viewAfterAdd' => 'set-selling_payment_methods',
                    'enableAddButton' => true,
                    'showRowsList' => true,
                    'createRelationIfEmpty' => true,
                    'relation' => [
                        'model' => CurrencyExchangeOrderSellingPaymentMethod::class,
                        'attributes' => [
                            'order_id' => [CurrencyExchangeOrder::class, 'id'],
                            'payment_method_id' => [PaymentMethodCurrencyByCurrency::class, 'payment_method_id'],
                        ],
                        'removeOldRows' => true,
                    ],
                ],
                'selling_cash_on' => [
                    'buttons' => [
                        [
                            [
                                'text' => Yii::t('bot', 'YES'),
                                'callback' => function (CurrencyExchangeOrder $model) {
                                    $model->selling_cash_on = CurrencyExchangeOrder::CASH_ON;

                                    return $model;
                                },
                            ],
                        ],
                        [
                            [
                                'text' => Yii::t('bot', 'NO'),
                                'callback' => function (CurrencyExchangeOrder $model) {
                                    $model->selling_cash_on = CurrencyExchangeOrder::CASH_OFF;

                                    return $model;
                                },
                            ],
                        ],
                    ],
                ],
                'selling_location' => [
                    'hidden' => function () {
                        if ($this->field->get($this->modelName, 'selling_cash_on') === 1) {
                            return false;
                        } else {
                            return true;
                        }
                    },
                    'isRequired' => false,
                    'component' => LocationToArrayFieldComponent::class,
                    'fieldNames' => [
                        'selling_location_lat',
                        'selling_location_lon',
                    ],
                    'buttons' => [
                        [
                            [
                                'hideCondition' => !$this->getTelegramUser()->userLocation,
                                'text' => Yii::t('bot', 'MY LOCATION'),
                                'callback' => function (CurrencyExchangeOrder $model) {
                                    $latitude = $this->getTelegramUser()->userLocation->location_lat;
                                    $longitude = $this->getTelegramUser()->userLocation->location_lon;
                                    if ($latitude && $longitude) {
                                        $model->selling_location_lat = $latitude;
                                        $model->selling_location_lon = $longitude;

                                        return $model;
                                    }

                                    return null;
                                },
                            ],
                        ],
                    ],
                ],
                'selling_delivery_radius' => [
                    'hidden' => function () {
                        if ($this->field->get($this->modelName, 'selling_cash_on') === 1) {
                            return false;
                        } else {
                            return true;
                        }
                    },
                    'buttons' => [
                        [
                            [
                                'text' => Yii::t('bot', 'NO'),
                                'callback' => function (CurrencyExchangeOrder $model) {
                                    $model->selling_delivery_radius = 0;

                                    return $model;
                                },
                            ],
                        ],
                    ],
                ],
                'buyingPaymentMethods' => [
                    'isRequired' => false,
                    'view' => 'choose-buying_payment_methods',
                    'viewAfterAdd' => 'set-buying_payment_methods',
                    'enableAddButton' => true,
                    'showRowsList' => true,
                    'createRelationIfEmpty' => true,
                    'relation' => [
                        'model' => CurrencyExchangeOrderBuyingPaymentMethod::class,
                        'attributes' => [
                            'order_id' => [CurrencyExchangeOrder::class, 'id'],
                            'payment_method_id' => [PaymentMethodCurrencyByCurrency::class, 'payment_method_id'],
                        ],
                        'removeOldRows' => true,
                    ],
                ],
                'buying_cash_on' => [
                    'buttons' => [
                        [
                            [
                                'text' => Yii::t('bot', 'YES'),
                                'callback' => function (CurrencyExchangeOrder $model) {
                                    $model->buying_cash_on = CurrencyExchangeOrder::CASH_ON;

                                    return $model;
                                },
                            ],
                        ],
                        [
                            [
                                'text' => Yii::t('bot', 'NO'),
                                'callback' => function (CurrencyExchangeOrder $model) {
                                    $model->buying_cash_on = CurrencyExchangeOrder::CASH_OFF;

                                    return $model;
                                },
                            ],
                        ],
                    ],
                ],
                'buying_location' => [
                    'hidden' => function () {
                        if ($this->field->get($this->modelName, 'buying_cash_on') === 1) {
                            return false;
                        } else {
                            return true;
                        }
                    },
                    'isRequired' => false,
                    'component' => LocationToArrayFieldComponent::class,
                    'fieldNames' => [
                        'buying_location_lat',
                        'buying_location_lon',
                    ],
                    'buttons' => [
                        [
                            [
                                'hideCondition' => !$this->getTelegramUser()->userLocation,
                                'text' => Yii::t('bot', 'MY LOCATION'),
                                'callback' => function (CurrencyExchangeOrder $model) {
                                    $latitude = $this->getTelegramUser()->userLocation->location_lat;
                                    $longitude = $this->getTelegramUser()->userLocation->location_lon;
                                    if ($latitude && $longitude) {
                                        $model->buying_location_lat = $latitude;
                                        $model->buying_location_lon = $longitude;

                                        return $model;
                                    }

                                    return null;
                                },
                            ],
                        ],
                    ],
                ],
                'buying_delivery_radius' => [
                    'hidden' => function () {
                        if ($this->field->get($this->modelName, 'buying_cash_on') === 1) {
                            return false;
                        } else {
                            return true;
                        }
                    },
                    'buttons' => [
                        [
                            [
                                'text' => Yii::t('bot', 'NO'),
                                'callback' => function (CurrencyExchangeOrder $model) {
                                    $model->buying_delivery_radius = 0;

                                    return $model;
                                },
                            ],
                        ],
                    ],
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
                // Buttons for the Edit mode
                'selling_currency_edit' => [
                    'hidden' => true,
                    'buttons' => [
                        [[
                            'item' => 'selling_currency_label',
                        ]],
                        [[
                            'item' => 'selling_currency_min_amount',
                        ]],
                        [[
                            'item' => 'selling_currency_max_amount',
                        ]],
                        [[
                            'item' => 'selling_cash_on',
                        ]],
                        [[
                            'item' => 'selling_location',
                        ]],
                        [[
                            'item' => 'selling_delivery_radius',
                        ]],
                    ],
                 ],
                 'buying_currency_edit' => [
                    'hidden' => true,
                    'buttons' => [
                        [[
                            'item' => 'buying_currency_label',
                        ]],
                        [[
                            'item' => 'buying_cash_on',
                        ]],
                        [[
                            'item' => 'buying_location',
                        ]],
                        [[
                            'item' => 'buying_delivery_radius',
                        ]],
                    ],
                ],
                'selling_currency_label' => [
                    'hidden' => true,
                    'isRequired' => false,
                ],
                'buying_currency_label' => [
                    'hidden' => true,
                    'isRequired' => false,
                ],
            ],
        ];
    }

    /**
     * @param int $page
     * @return array
     */
    public function actionIndex($page = 1)
    {
        $this->getState()->clearInputRoute();

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

            $paginationButtons = PaginationButtons::build($pagination, function ($page) {
                return self::createRoute('index', [
                    'page' => $page,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $rowButtons[] = [
            'callback_data' => MenuController::createRoute(),
            'text' => Emoji::MENU,
        ];

        $matchesCount = $globalUser->getCurrencyExchangeOrderMatches()->count();

        if ($matchesCount) {
            $rowButtons[] = [
                'callback_data' => self::createRoute('all-matches'),
                'text' => Emoji::OFFERS . ' ' . $matchesCount,
            ];
        }

        $newMatchesCount = $globalUser->getCurrencyExchangeOrderNewMatches()->count();

        if ($newMatchesCount) {
            $rowButtons[] = [
                'callback_data' => self::createRoute('all-new-matches'),
                'text' => Emoji::OFFERS . ' ' . Emoji::NEW1,
            ];
        }

        $rowButtons[] = [
            'callback_data' => self::createRoute('create'),
            'text' => Emoji::ADD,
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

        $this->getState()->clearInputRoute();

        $buttons = [
            [[
                'callback_data' => self::createRoute('set-status', [
                    'id' => $order->id,
                ]),
                'text' => $order->isActive() ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
            ]],
            [[
                'callback_data' => self::createRoute('e-a', [
                    'id' => $order->id,
                    'a' => 'selling_rate',
                ]),
                'text' => Yii::t('bot', $order->getAttributeLabel('selling_rate')) . ($order->selling_rate ? ': ' . ($order->selling_rate > 1 ? (float) $order->selling_rate : $order->selling_rate) : ''),
            ]],
            [[
                'callback_data' => self::createRoute('e-a', [
                    'id' => $order->id,
                    'a' => 'buying_rate',
                ]),
                'text' => Yii::t('bot', $order->getAttributeLabel('buying_rate')) . ($order->buying_rate ? ': ' . ($order->buying_rate > 1 ? (float) $order->buying_rate : $order->buying_rate) : ''),
            ]],
        ];

        $rowButtons[] = [
            'callback_data' => self::createRoute(),
            'text' => Emoji::BACK,
        ];

        $rowButtons[] = [
            'callback_data' => MenuController::createRoute(),
            'text' => Emoji::MENU,
        ];

        $matchesCount = $order->getMatches()->count();

        if ($matchesCount) {
            $rowButtons[] = [
                'callback_data' => self::createRoute('matches', [
                    'id' => $order->id,
                ]),
                'text' => Emoji::OFFERS . ' ' . $matchesCount,
            ];
        }

        $newMatchesCount = $order->getNewMatches()->count();

        if ($newMatchesCount) {
            $rowButtons[] = [
                'callback_data' => self::createRoute('new-matches', [
                    'id' => $order->id,
                ]),
                'text' => Emoji::OFFERS . ' ' . Emoji::NEW1,
            ];
        }

        $rowButtons[] = [
            'callback_data' => self::createRoute('update', [
                'id' => $order->id,
            ]),
            'text' => Emoji::EDIT,
        ];

        $buttons[] = $rowButtons;

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
     * @param int $id CurrencyExchangeOrder->id
     * @param int $page
     * @return array
     */
    public function actionMatches($id = null, $page = 1)
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

        $query = $order->getMatches()
            ->orderByRank();

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 1,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $orderMatch = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->one();

        if (!$orderMatch) {
            return $this->actionView($order->id);
        }

        $this->getState()->clearInputRoute();

        $matchOrder = $orderMatch->matchOrder;

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

        $isNewMatch = false;

        if ($orderMatch->isNew()) {
            $isNewMatch = true;

            $matchOrder->markViewedByUserId($this->globalUser->id);
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('match', [
                    'model' => $matchOrder,
                    'isNewMatch' => $isNewMatch,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @param int $id CurrencyExchangeOrder->id
     * @return array
     */
    public function actionNewMatches($id = null)
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

        $orderMatch = $order->getNewMatches()
            ->orderByRank()
            ->one();

        if (!$orderMatch) {
            return $this->actionMatches($order->id);
        }

        $this->getState()->clearInputRoute();

        $matchOrder = $orderMatch->matchOrder;

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'id' => $order->id,
                ]),
                'text' => '#' . $order->id . ' ' . $order->getTitle(),
            ]
        ];

        $buttons[] = [
            [
                'callback_data' => self::createRoute('new-matches', [
                    'id' => $order->id,
                ]),
                'text' => '>',
            ],
        ];

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

        $isNewMatch = false;

        if ($orderMatch->isNew()) {
            $isNewMatch = true;

            $matchOrder->markViewedByUserId($this->globalUser->id);
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('match', [
                    'model' => $matchOrder,
                    'isNewMatch' => $isNewMatch,
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
     * @return array
     */
    public function actionAllMatches($page = 1)
    {
        $query = $this->globalUser->getCurrencyExchangeOrderMatches()
            ->orderByRank();

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 1,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $orderMatch = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->one();

        if (!$orderMatch) {
            return $this->actionIndex();
        }

        $this->getState()->clearInputRoute();

        $order = $orderMatch->order;
        $matchOrder = $orderMatch->matchOrder;

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'id' => $order->id,
                ]),
                'text' => '#' . $order->id . ' ' . $order->getTitle(),
            ]
        ];

        $buttons[] = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('all-matches', [
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

        $isNewMatch = false;

        if ($orderMatch->isNew()) {
            $isNewMatch = true;

            $matchOrder->markViewedByUserId($this->globalUser->id);
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('match', [
                    'model' => $matchOrder,
                    'isNewMatch' => $isNewMatch,
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
    public function actionAllNewMatches()
    {
        $orderMatch = $this->globalUser->getCurrencyExchangeOrderNewMatches()
            ->orderByRank()
            ->one();

        if (!$orderMatch) {
            return $this->actionAllMatches();
        }

        $this->getState()->clearInputRoute();

        $order = $orderMatch->order;
        $matchOrder = $orderMatch->matchOrder;

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'id' => $order->id,
                ]),
                'text' => '#' . $order->id . ' ' . $order->getTitle(),
            ]
        ];

        $buttons[] = [
            [
                'callback_data' => self::createRoute('all-new-matches'),
                'text' => '>',
            ],
        ];

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

        $isNewMatch = false;

        if ($orderMatch->isNew()) {
            $isNewMatch = true;

            $matchOrder->markViewedByUserId($this->globalUser->id);
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('match', [
                    'model' => $matchOrder,
                    'isNewMatch' => $isNewMatch,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @param int $id CurrencyExchangeOrder->id
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

    /**
     * @param int $id CurrencyExchangeOrder->id
     * @return array
     */
    public function actionDelete($id = null)
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

        $model->delete();

        return $this->actionIndex();
    }
}
