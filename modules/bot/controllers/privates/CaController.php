<?php

namespace app\modules\bot\controllers\privates;

use app\behaviors\SetAttributeValueBehavior;
use app\behaviors\SetDefaultCurrencyBehavior;
use app\models\Currency;
use app\models\CurrencyExchangeOrder;
use app\models\CurrencyExchangeOrderBuyingPaymentMethod;
use app\models\CurrencyExchangeOrderMatch;
use app\models\CurrencyExchangeOrderSellingPaymentMethod;
use app\models\matchers\CurrencyExchangeOrderMatcher;
use app\models\PaymentMethod;
use app\models\User;
use app\modules\bot\components\Controller;
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
use app\modules\bot\components\crud\CrudController;

/**
 * Class CaController
 *
 * @link https://opensourcewebsite.org/currency-exchange-order
 * @package app\modules\bot\controllers\privates
 */
class CaController extends CrudController
{   
    protected function rules()
    {
        return [
            'model' => CurrencyExchangeOrder::class,
            'prepareViewParams' => function ($params) {
                /** @var CurrencyExchangeOrder $model */
                $model = $params['model'] ?? null;

                return [
                    'model' => $model,
                ];
            },
            'isVirtual' => true,
            'attributes' => [
                'sellingCurrency' => [
                    'buttons' => [
                        [   
                            'hideCondition' => ($this->field->get('currencyexchangeorder', 'selling_currency_id') === null),
                            'text' => Yii::t('bot', 'NEXT'),
                            'callback' => function (CurrencyExchangeOrder $model) {
                                return $model;
                            },
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ],
                    'view' => 'set-selling_currency',
                    'relation' => [
                        'attributes' => [
                            'selling_currency_id' => [Currency::class, 'id', 'code'],
                        ],
                    ],
                ],
                'buyingCurrency' => [
                    'buttons' => [
                        [   
                            'hideCondition' => ($this->field->get('currencyexchangeorder', 'buying_currency_id') === null),
                            'text' => Yii::t('bot', 'NEXT'),
                            'callback' => function (CurrencyExchangeOrder $model) {
                                return $model;
                            },
                        ],
                    ],
                    'view' => 'set-buying_currency',
                    'relation' => [
                        'attributes' => [
                            'buying_currency_id' => [Currency::class, 'id', 'code'],
                        ],
                    ],
                ],
                'selling_location' => [
                    'component' => LocationToArrayFieldComponent::class,
                    'fieldNames' => [
                        'selling_location_lat',
                        'selling_location_lon',
                    ],
                    'buttons' => [
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
                        [   
                            'hideCondition' => ($this->field->get('currencyexchangeorder', 'selling_location_lat') === null),
                            'text' => Yii::t('bot', 'NEXT'),
                            'callback' => function (CurrencyExchangeOrder $model) {
                                return $model;
                            },
                        ],
                    ],
                ],
                'selling_delivery_radius' => [
                    'buttons' => [
                        [
                            'text' => Yii::t('bot', 'NO'),
                            'callback' => function (CurrencyExchangeOrder $model) {
                                $model->selling_delivery_radius = 0;

                                return $model;
                            },
                        ],
                        [   
                            'hideCondition' => ($this->field->get('currencyexchangeorder', 'selling_delivery_radius') === null),
                            'text' => Yii::t('bot', 'NEXT'),
                            'callback' => function (CurrencyExchangeOrder $model) {
                                return $model;
                            },
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
            ],
        ];
    }
    /**
     * @return array
     */
    public function actionIndex()
    {
        return $this->actionCreate();
    }

    public function actionView($id = null)
    {
        return $this->actionMatches();
    }

    /**
     * @param int $page
     *
     * @return array
     */
    public function actionMatches($page = 1)
    {
        $attributes = [
            'selling_currency_id', 'buying_currency_id', 'selling_delivery_radius', 'selling_location_lat', 'selling_location_lon'
        ];
        $order_search = [];

        array_map(function ($attribute) use (&$order_search) {
            return $order_search[$attribute] = $this->field->get($this->modelName, $attribute);
        },
            $attributes);

        $order_search = array_merge(
        ['user_id' => $this->getUser()],
            $order_search
        );

        $order = new CurrencyExchangeOrder($order_search);

        if (!isset($order)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute(),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        $query = $order->getCashMatchesOrderByRank();
        $matchesCount = $query->count();

        if (!$matchesCount) {
            return $this->getResponseBuilder()
                ->editMessageTextOrSendMessage(
                    $this->render('no-matches', [
                        'model' => $order,
                    ]),
                    $buttons,
                    [
                        'disablePreview' => true,
                    ]
                )
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
        
        $matchOrder = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->one();

        if (!$matchOrder) {
            return $this->getResponseBuilder()
                ->editMessageTextOrSendMessage(
                    $this->render('no-matches', [
                        'model' => $order,
                    ]),
                    $buttons,
                    [
                        'disablePreview' => true,
                    ]
                )
                ->build();
        }

        $pagination_buttons = PaginationButtons::build($pagination, function ($page) use ($order) {
            return self::createRoute('matches', [
                'page' => $page,
            ]);
        });

        $buttons = [$pagination_buttons, ...$buttons];

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
}
