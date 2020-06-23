<?php

namespace app\modules\bot\controllers\privates;

use app\behaviors\SetAttributeValueBehavior;
use app\modules\bot\components\CrudController;
use app\modules\bot\components\rules\ExplodeStringFieldComponent;
use app\modules\bot\components\rules\LocationToArrayFieldComponent;
use app\modules\bot\models\AdSearchKeyword;
use Yii;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\models\AdSection;
use app\modules\bot\models\AdKeyword;
use app\modules\bot\models\AdOffer;
use app\modules\bot\models\AdSearch;
use yii\data\Pagination;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\User as TelegramUser;
use app\models\User;
use app\models\Currency;
use yii\db\ActiveRecord;

/**
 * Class SAdSearchController
 *
 * @package app\modules\bot\controllers\privates
 */
class SAdSearchController extends CrudController
{
    /** @inheritDoc */
    protected function rules()
    {
        return [
            [
                'model' => AdSearch::class,
                'prepareViewParams' => function ($params) {
                    $model = $params['model'] ?? null;

                    return [
                        'sectionName' => AdSection::getAdSearchName($model->section),
                        'keywords' => self::getKeywordsAsString(
                            $model->getKeywords()->all()
                        ),
                        'adSearch' => $model,
                        'currency' => isset($model->currency_id) ? Currency::findOne(
                            $model->currency_id
                        ) : null,
                        'locationLink' => ExternalLink::getOSMLink(
                            $model->location_lat,
                            $model->location_lon
                        ),
                        'liveDays' => AdSearch::LIVE_DAYS,
                        'showDetailedInfo' => true,
                    ];
                },
                'view' => 'search',
                'attributes' => [
                    'title' => [],
                    'description' => [
                        'isRequired' => false,
                    ],
                    'user_id' => [
                        'behaviors' => [
                            'SetAttributeValueBehavior' => [
                                'class' => SetAttributeValueBehavior::class,
                                'attribute' => 'user_id',
                                'value' => $this->module->user->id,
                            ],
                        ],
                        'hidden' => true,
                    ],
                    'section' => [
                        'behaviors' => [
                            'SetAttributeValueBehavior' => [
                                'class' => SetAttributeValueBehavior::class,
                                'attribute' => 'section',
                                'value' => AdSection::BUY_SELL,
                            ],
                        ],
                        'hidden' => true,
                    ],
                    'status' => [
                        'behaviors' => [
                            'SetAttributeValueBehavior' => [
                                'class' => SetAttributeValueBehavior::class,
                                'attributes' => [
                                    ActiveRecord::EVENT_BEFORE_VALIDATE => ['status'],
                                    ActiveRecord::EVENT_BEFORE_INSERT => ['status'],
                                ],
                                'attribute' => 'status',
                                'value' => AdSearch::STATUS_OFF,
                            ],
                        ],
                        'hidden' => true,
                    ],
                    'keywords' => [
                        //'enableAddButton' = true,
                        'isRequired' => false,
                        'relation' => [
                            'model' => AdSearchKeyword::class,
                            'attributes' => [
                                'ad_search_id' => [AdSearch::class, 'id'],
                                'ad_keyword_id' => [AdKeyword::class, 'id', 'keyword'],
                            ],
                        ],
                        'component' => [
                            'class' => ExplodeStringFieldComponent::class,
                            'attributes' => [
                                'delimiters' => [',', '.', "\n"],
                            ],
                        ],
                    ],
                    'currency' => [
                        'relation' => [
                            'attributes' => [
                                'currency_id' => [Currency::class, 'id', 'code'],
                            ],
                        ],
                    ],
                    'max_price' => [
                        'isRequired' => false,
                        'buttons' => [
                            [
                                'text' => Yii::t('bot', 'Edit currency'),
                                'item' => 'currency',
                            ],
                        ],
                        'systemButtons' => [
                            'back' => [
                                'item' => 'description',
                                'editMode' => false,
                            ],
                        ],
                        'prepareViewParams' => function ($params) {
                            /** @var AdSearch $model */
                            $model = $params['model'];
                            $currency = $model->currencyRelation;
                            if ($currency) {
                                $currencyCode = $currency->code;
                            } else {
                                $currencyCode = '';
                            }

                            return array_merge($params, [
                                'currencyCode' => $currencyCode,
                            ]);
                        },
                    ],
                    'location' => [
                        'component' => LocationToArrayFieldComponent::class,
                    ],
                    'pickup_radius' => [
                        'view' => 'edit-radius',
                        'buttons' => [
                            [
                                'text' => Yii::t('bot', 'No pickup'),
                                'callback' => function (AdSearch $model) {
                                    $model->pickup_radius = 0;

                                    return $model;
                                },
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param ActiveRecord $model
     * @param bool $isNew
     *
     * @return array
     */
    protected function afterSave(ActiveRecord $model, bool $isNew)
    {
        return $this->actionIndex($model->section);
    }

    public function actionIndex($adSection, $page = 1)
    {
        $this->getState()->setName(null);

        $buttons = [];

        $adSearchQuery = AdSearch::find()->where(
            [
                'user_id' => $this->getTelegramUser()->id,
                'section' => $adSection,
            ]
        );

        $adSearchCount = $adSearchQuery->count();

        $pagination = new Pagination(
            [
                'totalCount' => $adSearchCount,
                'pageSize' => 9,
                'params' => [
                    'page' => $page,
                ],
                'pageSizeParam' => false,
                'validatePage' => true,
            ]
        );

        foreach ($adSearchQuery
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->all() as $adSearch) {
            $buttons[][] = [
                'callback_data' => self::createRoute('search', ['adSearchId' => $adSearch->id]),
                'text' => ($adSearch->isActive() ? '' : '❌ ') . $adSearch->title,
            ];
        }

        $buttons[] = PaginationButtons::build(
            $pagination,
            function ($page) use ($adSection) {
                return self::createRoute(
                    'index',
                    [
                        'adSection' => $adSection,
                        'page' => $page,
                    ]
                );
            }
        );

        $buttons[] = [
            [
                'callback_data' => SAdController::createRoute(),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        if ($adSection == 1) {
            $buttons[count($buttons) - 1][] = [
                'callback_data' => self::createRoute(
                    'create',
                    [
                        //                'adSection' => $adSection
                        'm' => $this->getModelName(AdSearch::class),
                    ]
                ),
                'text' => Emoji::ADD,
            ];
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render(
                    'index',
                    [
                        'sectionName' => AdSection::getAdSearchName($adSection),
                        'inDevelopment' => ($adSection != 1),
                    ]
                ),
                $buttons
            )
            ->build();
    }

    private static function getKeywordsAsString($adKeywords)
    {
        $keywords = [];

        foreach ($adKeywords as $adKeyword) {
            $keywords[] = $adKeyword->keyword;
        }

        return implode(', ', $keywords);
    }

    public function actionAdd($adSection)
    {
        $this->getState()->setIntermediateField('adSearchSection', $adSection);

        $this->getState()->setName(self::createRoute('title-send'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-title'),
                [
                    [
                        [
                            'callback_data' => self::createRoute(
                                'index',
                                [
                                    'adSection' => $this->getState()->getIntermediateField(
                                        'adSearchSection'
                                    ),
                                ]
                            ),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionTitleSend()
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            $this->getState()->setIntermediateField('adSearchTitle', $message->getText());

            return $this->actionTitle();
        } else {
            return $this->actionAdd($this->getState()->getIntermediateField('adSearchSection'));
        }
    }

    public function actionTitle()
    {
        $this->getState()->setName(self::createRoute('description-send'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-description'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('description-skip'),
                            'text' => Yii::t('bot', 'Skip'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute(
                                'create',
                                [
                                    //                                'adSection' => $this->getState()->getIntermediateField('adSearchSection'),
                                    'm' => $this->getModelName(AdSearch::class),
                                ]
                            ),
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

    public function actionDescriptionSkip()
    {
        $this->getState()->setIntermediateField('adSearchDescription', null);

        return $this->actionDescription();
    }

    public function actionDescriptionSend()
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            $this->getState()->setIntermediateField('adSearchDescription', $message->getText());

            return $this->actionDescription();
        } else {
            return $this->actionTitle();
        }
    }

    public function actionDescription()
    {
        $this->getState()->setName(self::createRoute('keywords'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-keywords'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('keywords-skip'),
                            'text' => Yii::t('bot', 'Skip'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('title'),
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

    public function actionKeywordsSkip()
    {
        $this->getState()->setIntermediateFieldArray('adSearchKeywords', []);

        return $this->actionKeywords();
    }

    public function actionKeywords($page = 1)
    {
        if ($message = $this->getUpdate()->getMessage()) {
            $keywords = SAdOfferController::parseKeywords($message->getText());

            if (empty($keywords)) {
                return $this->actionTitle();
            }

            $adSearchKeywords = [];

            foreach ($keywords as $keyword) {
                $adKeyword = AdKeyword::find()->where(
                    [
                        'keyword' => $keyword,
                    ]
                )->one();

                if (!isset($adKeyword)) {
                    $adKeyword = new AdKeyword();

                    $adKeyword->setAttributes(
                        [
                            'keyword' => $keyword,
                        ]
                    );
                    $adKeyword->save();
                }

                $adSearchKeywords[] = $adKeyword->id;
            }
            $this->getState()->setIntermediateFieldArray('adSearchKeywords', $adSearchKeywords);
        }

        $this->getState()->setName(null);

        $currencyQuery = Currency::find();

        $telegramUser = $this->getTelegramUser();
        if ($telegramUser->user_id && User::findOne($telegramUser->user_id)) {
            $user = User::findOne($telegramUser->user_id);

            if ($user->currency_id !== null) {
                return $this->actionCurrencySet($user->currency_id);
            }
        }

        $pagination = new Pagination(
            [
                'totalCount' => $currencyQuery->count(),
                'pageSize' => 9,
                'params' => [
                    'page' => $page,
                ],
                'pageSizeParam' => false,
                'validatePage' => true,
            ]
        );

        $buttons = [];
        foreach ($currencyQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all() as $currency) {
            $buttons[][] = [
                'callback_data' => self::createRoute(
                    'currency-set',
                    [
                        'currencyId' => $currency->id,
                    ]
                ),
                'text' => $currency->code . ' - ' . $currency->name,
            ];
        }

        $buttons[] = PaginationButtons::build(
            $pagination,
            function ($page) {
                return self::createRoute('keywords', ['page' => $page]);
            }
        );

        $buttons[][] = [
            'callback_data' => self::createRoute('currency-skip'),
            'text' => Yii::t('bot', 'Skip'),
        ];

        $buttons[] = [
            [
                'callback_data' => self::createRoute('description'),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-currency'),
                $buttons
            )
            ->build();
    }

    public function actionCurrencySet($currencyId)
    {
        $this->getState()->setIntermediateField('adSearchCurrencyId', $currencyId);

        return $this->actionCurrency();
    }

    public function actionCurrency()
    {
        $this->getState()->setName(self::createRoute('max-price-send'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render(
                    'edit-max-price',
                    [
                        'currencyCode' => Currency::findOne(
                            $this->getState()->getIntermediateField('adSearchCurrencyId')
                        )->code,
                    ]
                ),
                [
                    [
                        [
                            'callback_data' => self::createRoute('change-currency'),
                            'text' => Yii::t('bot', 'Edit currency'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('max-price-skip'),
                            'text' => Yii::t('bot', 'Skip'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('title'),
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

    public function actionChangeCurrency($page = 1)
    {
        $currencyQuery = Currency::find();

        $pagination = new Pagination(
            [
                'totalCount' => $currencyQuery->count(),
                'pageSize' => 9,
                'params' => [
                    'page' => $page,
                ],
                'pageSizeParam' => false,
                'validatePage' => true,
            ]
        );

        $buttons = [];
        foreach ($currencyQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all() as $currency) {
            $buttons[][] = [
                'callback_data' => self::createRoute(
                    'currency-set',
                    [
                        'currencyId' => $currency->id,
                    ]
                ),
                'text' => $currency->code . ' - ' . $currency->name,
            ];
        }

        $buttons[] = PaginationButtons::build(
            $pagination,
            function ($page) {
                return self::createRoute('change-currency', ['page' => $page]);
            }
        );

        $buttons[] = [
            [
                'callback_data' => self::createRoute('currency'),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-currency'),
                $buttons
            )
            ->build();
    }

    public function actionCurrencySkip()
    {
        $this->getState()->setIntermediateField('adSearchCurrencyId', null);

        return $this->actionMaxPriceSkip();
    }

    public function actionMaxPriceSend()
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            if (!AdOffer::validatePrice($message->getText())) {
                return $this->actionKeywords();
            }

            $maxPrice = $message->getText();

            $this->getState()->setIntermediateField('adSearchMaxPrice', $maxPrice);

            return $this->actionMaxPrice();
        }
    }

    public function actionMaxPriceSkip()
    {
        $this->getState()->setIntermediateField('adSearchMaxPrice', null);

        return $this->actionMaxPrice();
    }

    public function actionMaxPrice()
    {
        $buttons = [];

        if ($this->getTelegramUser()->location_lat !== null) {
            $buttons[][] = [
                'callback_data' => self::createRoute('location-my'),
                'text' => Yii::t('bot', 'My location'),
            ];
        }

        $buttons[] = [
            [
                'callback_data' => $this->getState()->getIntermediateField(
                    'adSearchCurrencyId'
                ) === null ? self::createRoute('keywords') : self::createRoute('currency'),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        $this->getState()->setName(self::createRoute('location-send'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-location'),
                $buttons
            )
            ->build();
    }

    public function actionLocationMy()
    {
        $latitude = $this->getTelegramUser()->location_lat;
        $longitude = $this->getTelegramUser()->location_lon;

        return $this->actionLocationSet($latitude, $longitude);
    }

    public function actionLocationSend()
    {
        $message = $this->getUpdate()->getMessage();

        if ($message && $message->getLocation()) {
            $latitude = $message->getLocation()->getLatitude();
            $longitude = $message->getLocation()->getLongitude();
        } elseif ($message && $message->getText() && AdOffer::validateLocation($message->getText())) {
            $latitude = AdOffer::getLatitudeFromText($message->getText());
            $longitude = AdOffer::getLongitudeFromText($message->getText());
        } else {
            $latitude = null;
            $longitude = null;
        }

        return $this->actionLocationSet($latitude, $longitude);
    }

    public function actionLocationSet($latitude, $longitude)
    {
        if ($latitude && $longitude) {
            $this->getState()->setIntermediateField('adSearchLocationLatitude', strval($latitude));
            $this->getState()->setIntermediateField('adSearchLocationLongitude', strval($longitude));

            return $this->actionLocation();
        } else {
            return $this->actionMaxPrice();
        }
    }

    public function actionLocation()
    {
        $this->getState()->setName(self::createRoute('radius'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-radius'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('radius-skip'),
                            'text' => Yii::t('bot', 'No pickup'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('max-price'),
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

    public function actionRadiusSkip()
    {
        $this->getState()->setIntermediateField('adSearchRadius', '0');

        return $this->actionMakeSearch();
    }

    public function actionRadius()
    {
        $message = $this->getUpdate()->getMessage();

        if (!AdOffer::validateRadius($message->getText())) {
            return $this->actionLocation();
        }

        $radius = min(intval($message->getText()), AdOffer::MAX_RADIUS);

        $this->getState()->setIntermediateField('adSearchRadius', $radius);

        return $this->actionMakeSearch();
    }

    public function actionMakeSearch()
    {
        $adSearch = new AdSearch();

        $state = $this->getState();

        $adSearch->setAttributes(
            [
                'user_id' => $this->getTelegramUser()->id,
                'section' => intval($state->getIntermediateField('adSearchSection')),
                'title' => $state->getIntermediateField('adSearchTitle'),
                'description' => $state->getIntermediateField('adSearchDescription'),
                'pickup_radius' => doubleval($state->getIntermediateField('adSearchRadius')),
                'currency_id' => $state->getIntermediateField('adSearchCurrencyId') ? intval(
                    $state->getIntermediateField('adSearchCurrencyId')
                ) : null,
                'max_price' => $state->getIntermediateField('adSearchMaxPrice') ? intval(
                    $state->getIntermediateField('adSearchMaxPrice')
                ) : null,
                'location_lat' => $state->getIntermediateField('adSearchLocationLatitude'),
                'location_lon' => $state->getIntermediateField('adSearchLocationLongitude'),
                'created_at' => time(),
                'renewed_at' => time(),
                'status' => AdSearch::STATUS_OFF,
                'edited_at' => null,
            ]
        );

        $adSearch->save();

        foreach ($this->getState()->getIntermediateFieldArray('adSearchKeywords') as $adKeywordId) {
            $adKeyword = AdKeyword::findOne($adKeywordId);

            $adSearch->link('keywords', $adKeyword);
        }

        return $this->actionSearch($adSearch->id);
    }

    public function actionSearch($adSearchId)
    {
        $this->updateSearch($adSearchId);

        $adSearch = AdSearch::findOne($adSearchId);
        $buttons = [];

        $buttons[][] = [
            'callback_data' => self::createRoute('status', ['adSearchId' => $adSearchId]),
            'text' => 'Status: ' . ($adSearch->isActive() ? 'ON' : 'OFF'),
        ];

        $matchedAdOfferCount = $adSearch->getMatches()->count();

        if ($matchedAdOfferCount > 0) {
            $buttons[][] = [
                'callback_data' => self::createRoute('ad-offer-matches', ['adSearchId' => $adSearchId]),
                'text' => '🙋‍♂️ ' . $matchedAdOfferCount,
            ];
        }
        $buttons[] = [
            [
                'callback_data' => self::createRoute('index', ['adSection' => $adSearch->section]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'callback_data' => self::createRoute(
                    'u',
                    [
                        'm' => $this->getModelName(AdSearch::class),
                        'i' => $adSearchId,
                    ]
                ),
                'text' => Emoji::EDIT,
            ],
            [
                'callback_data' => self::createRoute('delete', ['adSearchId' => $adSearchId]),
                'text' => Emoji::DELETE,
            ],
        ];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render(
                    'search',
                    [
                        'sectionName' => AdSection::getAdSearchName($adSearch->section),
                        'keywords' => self::getKeywordsAsString(
                            $adSearch->getKeywords()->all()
                        ),
                        'adSearch' => $adSearch,
                        'currency' => isset($adSearch->currency_id) ? Currency::findOne(
                            $adSearch->currency_id
                        ) : null,
                        'locationLink' => ExternalLink::getOSMLink(
                            $adSearch->location_lat,
                            $adSearch->location_lon
                        ),
                        'liveDays' => AdSearch::LIVE_DAYS,
                        'showDetailedInfo' => true,
                    ]
                ),
                $buttons,
                true
            )
            ->build();
    }

    public function updateSearch($adSearchId)
    {
        $adSearch = AdSearch::findOne($adSearchId);

        $adSearch->setAttributes(
            [
                'renewed_at' => time(),
            ]
        );

        $adSearch->save();
    }

    public function actionEdit($adSearchId)
    {
        $this->getState()->setName(null);

        $adSearch = AdSearch::findOne($adSearchId);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render(
                    'search',
                    [
                        'sectionName' => AdSection::getAdSearchName($adSearch->section),
                        'keywords' => self::getKeywordsAsString(
                            $adSearch->getKeywords()->all()
                        ),
                        'adSearch' => $adSearch,
                        'currency' => isset($adSearch->currency_id) ? Currency::findOne(
                            $adSearch->currency_id
                        ) : null,
                        'locationLink' => ExternalLink::getOSMLink(
                            $adSearch->location_lat,
                            $adSearch->location_lon
                        ),
                        'liveDays' => AdSearch::LIVE_DAYS,
                        'showDetailedInfo' => false,
                    ],
                ),
                [
                    [
                        [
                            'callback_data' => self::createRoute(
                                'edit-title',
                                ['adSearchId' => $adSearchId]
                            ),
                            'text' => Yii::t('bot', 'Title'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute(
                                'edit-description',
                                ['adSearchId' => $adSearchId]
                            ),
                            'text' => Yii::t('bot', 'Description'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute(
                                'edit-keywords',
                                ['adSearchId' => $adSearchId]
                            ),
                            'text' => Yii::t('bot', 'Keywords'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute(
                                'edit-max-price',
                                ['adSearchId' => $adSearchId]
                            ),
                            'text' => Yii::t('bot', 'Max price'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute(
                                'edit-location',
                                ['adSearchId' => $adSearchId]
                            ),
                            'text' => Yii::t('bot', 'Location'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute(
                                'edit-radius',
                                ['adSearchId' => $adSearchId]
                            ),
                            'text' => Yii::t('bot', 'Pickup radius'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute(
                                'search',
                                ['adSearchId' => $adSearchId]
                            ),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ],
                ],
                true
            )
            ->build();
    }

    public function actionEditTitle($adSearchId)
    {
        $this->getState()->setName(
            self::createRoute(
                'new-title',
                [
                    'adSearchId' => $adSearchId,
                ]
            )
        );

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-title'),
                [
                    [
                        [
                            'callback_data' => self::createRoute(
                                'u',
                                [
                                    'm' => $this->getModelName(AdSearch::class),
                                    'i' => $adSearchId,
                                ]
                            ),
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

    public function actionNewTitle($adSearchId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            $adSearch = AdSearch::findOne($adSearchId);

            $adSearch->setAttributes(
                [
                    'title' => $message->getText(),
                ]
            );

            $adSearch->save();

            return $this->actionSearch($adSearchId);
        } else {
            return $this->actionEditTitle($adSearchId);
        }
    }

    public function actionEditDescription($adSearchId)
    {
        $this->getState()->setName(
            self::createRoute(
                'new-description',
                [
                    'adSearchId' => $adSearchId,
                ]
            )
        );

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-description'),
                [
                    [
                        [
                            'callback_data' => self::createRoute(
                                'new-description-skip',
                                [
                                    'adSearchId' => $adSearchId,
                                ]
                            ),
                            'text' => Yii::t('bot', 'No'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute(
                                'u',
                                [
                                    'm' => $this->getModelName(AdSearch::class),
                                    'i' => $adSearchId,
                                ]
                            ),
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

    public function actionNewDescriptionSkip($adSearchId)
    {
        $adSearch = AdSearch::findOne($adSearchId);

        $adSearch->setAttributes(
            [
                'description' => null,
            ]
        );

        $adSearch->save();

        return $this->actionSearch($adSearchId);
    }

    public function actionNewDescription($adSearchId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            $adSearch = AdSearch::findOne($adSearchId);

            $adSearch->setAttributes(
                [
                    'description' => $message->getText(),
                ]
            );

            $adSearch->save();

            return $this->actionSearch($adSearchId);
        } else {
            return $this->actionEditDescription($adSearchId);
        }
    }

    public function actionEditCurrency($adSearchId, $page = 1)
    {
        $adSearch = AdSearch::findOne($adSearchId);

        $currencyQuery = Currency::find();

        $pagination = new Pagination(
            [
                'totalCount' => $currencyQuery->count(),
                'pageSize' => 9,
                'params' => [
                    'page' => $page,
                ],
                'pageSizeParam' => false,
                'validatePage' => true,
            ]
        );

        $buttons = [];

        $telegramUser = $this->getTelegramUser();
        $userCurrencyId = null;
        if ($telegramUser->user_id && User::findOne($telegramUser->user_id)) {
            $user = User::findOne($telegramUser->user_id);

            if ($user->currency_id !== null) {
                $userCurrencyId = $user->currency_id;

                $buttons[][] = [
                    'callback_data' => self::createRoute(
                        'edit-currency-set',
                        [
                            'adSearchId' => $adSearchId,
                            'currencyId' => $user->currency_id,
                        ]
                    ),
                    'text' => '· ' . Currency::findOne($user->currency_id)->code . ' - ' . Currency::findOne(
                            $user->currency_id
                        )->name . ' ·',
                ];
            }
        }

        foreach ($currencyQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all() as $currency) {
            $buttons[][] = [
                'callback_data' => self::createRoute(
                    'edit-currency-set',
                    [
                        'adSearchId' => $adSearchId,
                        'currencyId' => $currency->id,
                    ]
                ),
                'text' => $currency->code . ' - ' . $currency->name,
            ];
        }

        $buttons[] = PaginationButtons::build(
            $pagination,
            function ($page) use ($adSearchId) {
                return self::createRoute(
                    'edit-currency',
                    [
                        'adSearchId' => $adSearchId,
                        'page' => $page,
                    ]
                );
            }
        );

        $buttons[] = [
            [
                'callback_data' => isset($adSearch->currency_id)
                    ? self::createRoute('edit-max-price', ['adSearchId' => $adSearchId])
                    : self::createRoute(
                        'u',
                        [
                            'm' => $this->getModelName(AdSearch::class),
                            'i' => $adSearchId,
                        ]
                    ),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-currency'),
                $buttons
            )
            ->build();
    }

    public function actionEditCurrencySet($adSearchId, $currencyId)
    {
        $adSearch = AdSearch::findOne($adSearchId);

        $adSearch->setAttributes(
            [
                'currency_id' => $currencyId,
            ]
        );

        $adSearch->save();

        return $this->actionEditMaxPrice($adSearchId);
    }

    public function actionEditMaxPrice($adSearchId)
    {
        $adSearch = AdSearch::findOne($adSearchId);

        if (!isset($adSearch->currency_id)) {
            return $this->actionEditCurrency($adSearchId);
        }

        $this->getState()->setName(self::createRoute('edit-max-price-set', ['adSearchId' => $adSearchId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render(
                    'edit-max-price',
                    [
                        'currencyCode' => Currency::findOne($adSearch->currency_id)->code,
                    ]
                ),
                [
                    [
                        [
                            'callback_data' => self::createRoute(
                                'edit-currency',
                                ['adSearchId' => $adSearchId]
                            ),
                            'text' => Yii::t('bot', 'Edit currency'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute(
                                'u',
                                [
                                    'm' => $this->getModelName(AdSearch::class),
                                    'i' => $adSearchId,
                                ]
                            ),
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

    public function actionEditMaxPriceSet($adSearchId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            if (!AdOffer::validatePrice($message->getText())) {
                return $this->actionEditMaxPrice($adSearchId);
            }

            $maxPrice = $message->getText();

            $adSearch = AdSearch::findOne($adSearchId);

            $adSearch->setAttributes(
                [
                    'max_price' => $maxPrice,
                ]
            );
            $adSearch->save();

            return $this->actionSearch($adSearchId);
        }
    }

    public function actionEditKeywords($adSearchId)
    {
        $this->getState()->setName(self::createRoute('new-keywords', ['adSearchId' => $adSearchId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-keywords'),
                [
                    [
                        [
                            'callback_data' => self::createRoute(
                                'new-keywords-skip',
                                [
                                    'adSearchId' => $adSearchId,
                                ]
                            ),
                            'text' => Yii::t('bot', 'No'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute(
                                'u',
                                [
                                    'm' => $this->getModelName(AdSearch::class),
                                    'i' => $adSearchId,
                                ]
                            ),
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

    public function actionNewKeywordsSkip($adSearchId)
    {
        $adSearch = AdSearch::findOne($adSearchId);

        $adSearch->unlinkAll('keywords', true);

        $adSearch->markToUpdateMatches();

        return $this->actionSearch($adSearchId);
    }

    public function actionNewKeywords($adSearchId)
    {
        if ($message = $this->getUpdate()->getMessage()) {
            $keywords = SAdOfferController::parseKeywords($message->getText());

            if (empty($keywords)) {
                return $this->actionEditKeywords($adSearchId);
            }

            $adSearch = AdSearch::findOne($adSearchId);

            $adSearch->unlinkAll('keywords', true);
            foreach ($keywords as $keyword) {
                $adKeyword = AdKeyword::find()->where(
                    [
                        'keyword' => $keyword,
                    ]
                )->one();

                if (!isset($adKeyword)) {
                    $adKeyword = new AdKeyword();

                    $adKeyword->setAttributes(
                        [
                            'keyword' => $keyword,
                        ]
                    );
                    $adKeyword->save();
                }

                $adSearch->link('keywords', $adKeyword);
            }

            $adSearch->markToUpdateMatches();

            return $this->actionSearch($adSearchId);
        }
    }

    public function actionEditLocation($adSearchId)
    {
        $this->getState()->setName(self::createRoute('new-location-send', ['adSearchId' => $adSearchId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-location'),
                [
                    [
                        [
                            'callback_data' => self::createRoute(
                                'new-location-my',
                                ['adSearchId' => $adSearchId]
                            ),
                            'text' => Yii::t('bot', 'My location'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute(
                                'u',
                                [
                                    'm' => $this->getModelName(AdSearch::class),
                                    'i' => $adSearchId,
                                ]
                            ),
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

    public function actionNewLocationMy($adSearchId)
    {
        $latitude = $this->getTelegramUser()->location_lat;
        $longitude = $this->getTelegramUser()->location_lon;

        return $this->actionNewLocationSet($adSearchId, $latitude, $longitude);
    }

    public function actionNewLocationSend($adSearchId)
    {
        $message = $this->getUpdate()->getMessage();

        if ($message && $message->getLocation()) {
            $latitude = $message->getLocation()->getLatitude();
            $longitude = $message->getLocation()->getLongitude();
        } elseif ($message && $message->getText() && AdOffer::validateLocation($message->getText())) {
            $latitude = AdOffer::getLatitudeFromText($message->getText());
            $longitude = AdOffer::getLongitudeFromText($message->getText());
        } else {
            $latitude = null;
            $longitude = null;
        }

        return $this->actionNewLocationSet($adSearchId, $latitude, $longitude);
    }

    public function actionNewLocationSet($adSearchId, $latitude, $longitude)
    {
        if ($latitude && $longitude) {
            $adSearch = AdSearch::findOne($adSearchId);

            $adSearch->setAttributes(
                [
                    'location_lat' => strval($latitude),
                    'location_lon' => strval($longitude),
                ]
            );
            $adSearch->save();

            return $this->actionSearch($adSearchId);
        } else {
            return $this->actionEditLocation($adSearchId);
        }
    }

    public function actionEditRadius($adSearchId)
    {
        $this->getState()->setName(self::createRoute('new-radius', ['adSearchId' => $adSearchId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-radius'),
                [
                    [
                        [
                            'callback_data' => self::createRoute(
                                'new-radius-skip',
                                ['adSearchId' => $adSearchId]
                            ),
                            'text' => Yii::t('bot', 'No pickup'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute(
                                'u',
                                [
                                    'm' => $this->getModelName(AdSearch::class),
                                    'i' => $adSearchId,
                                ]
                            ),
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

    public function actionNewRadiusSkip($adSearchId)
    {
        $adSearch = AdSearch::findOne($adSearchId);

        if (isset($adSearch)) {
            $adSearch->setAttributes(
                [
                    'pickup_radius' => 0,
                ]
            );

            $adSearch->save();
        }

        return $this->actionSearch($adSearchId);
    }

    public function actionNewRadius($adSearchId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            if (!AdOffer::validateRadius($message->getText())) {
                return $this->actionEditRadius($adSearchId);
            }

            $radius = min(intval($message->getText()), AdOffer::MAX_RADIUS);

            $adSearch = AdSearch::findOne($adSearchId);

            $adSearch->setAttributes(
                [
                    'pickup_radius' => $radius,
                ]
            );
            $adSearch->save();
            $adSearch->markToUpdateMatches();

            return $this->actionSearch($adSearchId);
        }
    }

    public function actionStatus($adSearchId)
    {
        $adSearch = AdSearch::findOne($adSearchId);

        $adSearch->setAttributes(
            [
                'status' => ($adSearch->isActive() ? AdSearch::STATUS_OFF : AdSearch::STATUS_ON),
            ]
        );
        $adSearch->save();

        if ($adSearch->isActive()) {
            $adSearch->markToUpdateMatches();
        } else {
            $adSearch->unlinkAll('matches', true);
            $adSearch->setAttributes(
                [
                    'edited_at' => time(),
                ]
            );
            $adSearch->save();
        }

        return $this->actionSearch($adSearchId);
    }

    public function actionAdOfferMatches($adSearchId, $page = 1)
    {
        $adSearch = AdSearch::findOne($adSearchId);

        $adOfferQuery = $adSearch->getMatches();

        if ($adOfferQuery->count() == 0) {
            return $this->actionSearch($adSearchId);
        }

        $pagination = new Pagination(
            [
                'totalCount' => $adOfferQuery->count(),
                'pageSize' => 1,
                'params' => [
                    'page' => $page,
                ],
                'pageSizeParam' => false,
                'validatePage' => true,
            ]
        );

        $paginationButtons = PaginationButtons::build(
            $pagination,
            function ($page) use ($adSearchId) {
                return self::createRoute(
                    'ad-offer-matches',
                    [
                        'adSearchId' => $adSearchId,
                        'page' => $page,
                    ]
                );
            }
        );

        $buttons = [];

        $buttons[] = $paginationButtons;
        $buttons[] = [
            [
                'callback_data' => self::createRoute('search', ['adSearchId' => $adSearchId]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        $adOffer = $adOfferQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all()[0];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->sendPhotoOrEditMessageTextOrSendMessage(
                $adOffer->getPhotos()->count() ? $adOffer->getPhotos()->one()->file_id : null,
                $this->render(
                    'offer-matches',
                    [
                        'adOffer' => $adOffer,
                        'user' => TelegramUser::findOne($adOffer->user_id),
                        'currency' => Currency::findOne($adOffer->currency_id),
                        'sectionName' => AdSection::getAdOfferName($adOffer->section),
                        'keywords' => self::getKeywordsAsString($adOffer->getKeywords()->all()),
                        'locationLink' => ExternalLink::getOSMLink(
                            $adOffer->location_lat,
                            $adOffer->location_lon
                        ),
                    ]
                ),
                $buttons,
                true
            )
            ->build();
    }

    public function actionDelete($adSearchId)
    {
        $adSearch = AdSearch::findOne($adSearchId);

        if (isset($adSearch)) {
            $adSection = $adSearch->section;

            $adSearch->unlinkAll('keywords', true);

            $adSearch->delete();

            return $this->actionIndex($adSection);
        }
    }
}
