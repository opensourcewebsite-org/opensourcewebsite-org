<?php

namespace app\modules\bot\controllers\privates;

use app\models\CurrencyExchangeOrder;
use app\modules\bot\components\CrudController;
use app\modules\bot\components\helpers\Emoji;

use Yii;
use app\modules\bot\components\Controller;
use yii\data\Pagination;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\filters\AccessControl;

/**
 * Class SCeController
 *
 * @package app\modules\bot\controllers
 */
class SCeController extends CrudController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['create', 'update', 'delete', 'index', 'show'],
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function($rule, $action){
                            $telegramUser = $this->getTelegramUser();
                            return $telegramUser->location_lon && $telegramUser->location_lat && $telegramUser->provider_user_name;
                        }
                    ]
                ],
                'denyCallback' => function($rule, $action) {
                    return $this->redirect(['no-requirements']);
                }
            ]
        ];
    }

    protected function rules()
    {
        return [
            [
                'model' => CurrencyExchangeOrder::class,
                'attributes' => [
                    'sellingCurrency' => [
                        'relation' => [
                            'attributes' => [
                                'selling_currency_id' => [\app\models\Currency::class, 'id'],
                            ],
                        ],
                    ],
                    'buyingCurrency' => [
                        'relation' => [
                            'attributes' => [
                                'buying_currency_id' => [\app\models\Currency::class, 'id'],
                            ],
                        ],
                    ],
                    'selling_rate' => [],
                    'buying_rate' => [],
                    'selling_currency_min_amount' => [],
                    'selling_currency_max_amount' => [],
                ],
            ]
        ];
    }

    protected function getCurrencyLabel(\app\models\Currency $currency)
    {
        return $currency->code . ' - ' . $currency->name;
    }

    protected function getCurrencyexchangeorder(int $orderId)
    {
        return CurrencyExchangeOrder::find()
            ->joinWith(['sellingCurrency', 'buyingCurrency'])
            ->where(['{{%currency_exchange_order}}.id' => $orderId])
            ->one();
    }

    protected function getCurrencyExchangeOrderKeyboard(CurrencyExchangeOrder $order)
    {
        return [
            [
                [
                    'callback_data' => self::createRoute('order-status'),
                    'text' => 'Status: ' . ($order->status ? 'ON' : 'OFF'),
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
        ];

    }



    /**
     * @return array
     */
    public function actionIndex($page = 1)
    {

        $ordersQuery = CurrencyExchangeOrder::find()
            ->joinWith(['buyingCurrency', 'sellingCurrency']);

        $pagination = new Pagination([
            'totalCount' => $ordersQuery->count(),
            'pageSize' => 5,
            'params' => [
                'page' => $page,
            ]
        ]);

        $pagination->pageSizeParam = false;
        $pagination->validatePage = true;

        $orders = $ordersQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $buttons = [];
        /** @var CurrencyExchangeOrder $order */
        foreach ($orders as $order) {
            $buttons[][] = [
                'text' => $order->sellingCurrency->code .'/'. $order->buyingCurrency->code,
                'callback_data' => self::createRoute('show', [
                    'id' => $order->id,
                    'm' => $this->getModelName(CurrencyExchangeOrder::class),
                ]),
            ];
        }

//        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
//            return self::createRoute('index', [
//                'page' => $page,
//            ]);
//        });
//
//        $buttons[] = $paginationButtons;
        $tools = [
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
                'callback_data' => self::createRoute('create', [
                    'm' => $this->getModelName(CurrencyExchangeOrder::class),
                ]),
                'text' => Emoji::ADD,
            ],
        ];
        $buttons[] = $tools;

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                $buttons
            )
            ->build();
    }

    protected function beforeSave(ActiveRecord $model, bool $isNew)
    {
        $model->user_id = $this->getUser()->getId();
        $model->validate();
        Yii::warning(print_r($model->errors, true));
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
    public function actionNoRequirements()
    {
        return $this->getResponseBuilder()
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

    protected function afterSave(ActiveRecord $model, bool $isNew)
    {
        return $this->redirect(['index']); // temporary for now
    }
}
