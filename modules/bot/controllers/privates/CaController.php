<?php

namespace app\modules\bot\controllers\privates;

use app\behaviors\SetAttributeValueBehavior;
use app\behaviors\SetDefaultCurrencyBehavior;
use app\models\CashExchangeOrder;
use app\models\Currency;
use app\models\CurrencyExchangeOrder;
use app\models\CurrencyExchangeOrderBuyingPaymentMethod;
use app\models\CurrencyExchangeOrderMatch;
use app\models\CurrencyExchangeOrderSellingPaymentMethod;
use app\models\matchers\CurrencyExchangeOrderMatcher;
use app\models\PaymentMethod;
use app\models\User;
use app\modules\bot\components\Controller;
use app\modules\bot\components\crud\CrudController;
use app\modules\bot\components\crud\rules\ExplodeStringFieldComponent;
use app\modules\bot\components\crud\rules\LocationToArrayFieldComponent;
use app\modules\bot\components\crud\services\IntermediateFieldService;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\validators\RadiusValidator;
use Yii;
use yii\data\Pagination;
use yii\db\ActiveRecord;

/**
 * Class CaController
 *
 * @link https://opensourcewebsite.org/currency-exchange-order
 * @package app\modules\bot\controllers\privates
 */
class CaController extends CrudController
{
    public function init()
    {
        $this->enableGlobalBackRoute = true;
        $this->layout = 'main';
        parent::init();
    }

    protected $searchAttributes = [
        'selling_currency_id', 'buying_currency_id', 'selling_delivery_radius', 'selling_location_lat', 'selling_location_lon'
    ];

    protected function rules()
    {
        return [
            'model' => CashExchangeOrder::class,
            'prepareViewParams' => function ($params) {
                /** @var CashExchangeOrder $model */
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
                            'hideCondition' => $this->field->get($this->modelName, 'selling_currency_id') == null,
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'hideCondition' => !$this->field->get($this->modelName, 'selling_currency_id') == null,
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::BACK,
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
                            'hideCondition' => !isset($this->getTelegramUser()->userLocation),
                            'text' => Yii::t('bot', 'MY LOCATION'),
                            'callback' => function (CashExchangeOrder $model) {
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
                    'behaviors' => [
                        'SetAttributeValueBehavior' => [
                            'class' => SetAttributeValueBehavior::class,
                            'attributes' => [
                                ActiveRecord::EVENT_BEFORE_VALIDATE => ['selling_location'],
                                ActiveRecord::EVENT_BEFORE_INSERT => ['selling_location'],
                            ],
                            'attribute' => 'selling_location',
                            'value' => ($this->getTelegramUser()->userLocation !== null) ? $this->getTelegramUser()->userLocation->getLocation() : '',
                        ],
                    ],
                ],
                'selling_delivery_radius' => [
                    'buttons' => [
                        [
                            'text' => Yii::t('bot', 'NO'),
                            'callback' => function (CashExchangeOrder $model) {
                                $model->selling_delivery_radius = 0;

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
        $filled = true;

        foreach ($this->searchAttributes as $attributeName) {
            $attribute = $this->field->get($this->modelName, $attributeName);

            if (!isset($attribute)) {
                $filled = false;
            }
        }

        return ($filled === true) ? $this->actionView() : $this->actionCreate();
    }

    /**
     * @return array
     */
    public function actionDelete()
    {
        $modelName = $this->getModelName();
        $this->field->reset($modelName);

        return $this->getResponseBuilder()
            ->build();
    }

    /**
     * @param int $page
     * @return array
     */
    public function actionMatches($page = 1)
    {
        extract($this->getQueryParams());

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

        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('matches', [
                'page' => $page,
            ]);
        });

        if ($paginationButtons) {
            $buttons[] = $paginationButtons;
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

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('matches', [
                    'model' => $matchOrder,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    private function getQueryParams(): array
    {
        $order_search = [];

        array_map(
            function ($attribute) use (&$order_search) {
                return $order_search[$attribute] = $this->field->get($this->modelName, $attribute);
            },
            $this->searchAttributes
        );

        $order_search = array_merge(
            ['user_id' => $this->getUser()],
            $order_search
        );

        $order = new CashExchangeOrder($order_search);

        $query = $order->getCashMatchesOrderByRank();
        $matchesCount = $query->count();

        return compact('query', 'matchesCount', 'order');
    }

    public function actionView()
    {
        $rowButtons = [];
        extract($this->getQueryParams());

        if ($matchesCount) {
            $rowButtons[] = [
                'callback_data' => self::createRoute('matches', [
                    'matchesCount' => $matchesCount,
                ]),
                'text' => Emoji::OFFERS . ' ' . $matchesCount,
            ];
        }

        $editButtons = [];

        foreach (array_keys($this->attributes) as $attributeName) {
            $attribute = $this->attributes[$attributeName];
            $hidden = false;

            if (isset($attribute['hidden'])) {
                $hidden = $attribute['hidden'];

                if (is_callable($hidden)) {
                    $hidden = call_user_func($attribute['hidden'], []);
                }
            }

            if (!$hidden) {
                $editButtons[] =
                    [[
                        'text' => Yii::t('bot', $order->getAttributeLabel($attributeName)),
                        'callback_data' => self::createRoute('e-a', [
                            'a' => $attributeName
                        ]),
                    ]];
            }
        }

        $buttons = [$rowButtons, ...$editButtons];
        $buttons[] = [
            [
            'callback_data' => MenuController::createRoute(),
            'text' => Emoji::MENU,
            ]
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
}
