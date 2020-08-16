<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\crud\CrudController;
use app\behaviors\SetDefaultCurrencyBehavior;
use app\behaviors\SetAttributeValueBehavior;
use app\modules\bot\components\crud\rules\ExplodeStringFieldComponent;
use app\modules\bot\components\crud\rules\LocationToArrayFieldComponent;
use app\modules\bot\components\crud\services\IntermediateFieldService;
use app\modules\bot\validators\RadiusValidator;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\models\AdSection;
use app\models\AdSearchKeyword;
use app\models\AdKeyword;
use app\models\AdOffer;
use app\models\AdSearch;
use app\models\AdSearchMatch;
use yii\base\ModelEvent;
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
                        'section' => $model->section,
                        'keywords' => self::getKeywordsAsString($model->getKeywords()->all()),
                        'adSearch' => $model,
                        'currency' => isset($model->currency_id) ? Currency::findOne($model->currency_id) : null,
                        'locationLink' => ExternalLink::getOSMLink($model->location_lat, $model->location_lon),
                        'showDetailedInfo' => true,
                    ];
                },
                'view' => 'show',
                'attributes' => [
                    'title' => [],
                    'description' => [
                        'isRequired' => false,
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
                    'section' => [
                        'behaviors' => [
                            'SetAttributeValueBehavior' => [
                                'class' => SetAttributeValueBehavior::class,
                                'attributes' => [
                                    ActiveRecord::EVENT_BEFORE_VALIDATE => ['section'],
                                    ActiveRecord::EVENT_BEFORE_INSERT => ['section'],
                                ],
                                'attribute' => 'section',
                                'value' => $this->getState()
                                    ->getIntermediateField(IntermediateFieldService::SAFE_ATTRIBUTE),
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
                            'removeOldRows' => true,
                        ],
                        'component' => [
                            'class' => ExplodeStringFieldComponent::class,
                            'attributes' => [
                                'delimiters' => [',', '.', "\n"],
                            ],
                        ],
                    ],
                    'currency' => [
                        'behaviors' => [
                            'SetDefaultCurrencyBehavior' => [
                                'class' => SetDefaultCurrencyBehavior::class,
                                'telegramUser' => $this->getTelegramUser(),
                                'attributes' => [
                                    ActiveRecord::EVENT_BEFORE_VALIDATE => ['currency_id'],
                                    ActiveRecord::EVENT_BEFORE_INSERT => ['currency_id'],
                                ],
                            ],
                        ],
                        'hidden' => true,
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
                        'buttons' => [
                            [
                                'hideCondition' => !$this->getTelegramUser()->location_lat || !$this->getTelegramUser()->location_lon,
                                'text' => Yii::t('bot', 'My location'),
                                'callback' => function (AdSearch $model) {
                                    $latitude = $this->getTelegramUser()->location_lat;
                                    $longitude = $this->getTelegramUser()->location_lon;
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
                    'pickup_radius' => [
                        'view' => 'edit-radius',
                        'buttons' => [
                            [
                                'text' => Yii::t('bot', 'NO'),
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
        $model->markToUpdateMatches();

        return $this->actionView($model->id);
    }

    public function actionIndex($adSection, $page = 1)
    {
        $this->getState()->setName(null);
        $this->getState()->setIntermediateField(IntermediateFieldService::SAFE_ATTRIBUTE, $adSection);
        $user = $this->getUser();

        $adSearchQuery = AdSearch::find()
            ->where([
                'user_id' => $user->id,
                'section' => $adSection,
            ])
            ->orderBy([
                'status' => SORT_DESC,
                'title' => SORT_ASC,
            ]);

        $adSearchCount = $adSearchQuery->count();

        $pagination = new Pagination([
            'totalCount' => $adSearchCount,
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $adSearches = $adSearchQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $buttons = array_map(function ($adSearch) {
            return [
                [
                    'text' => ($adSearch->isActive() ? '' : Emoji::INACTIVE . ' ') . $adSearch->title,
                    'callback_data' => self::createRoute('view', [
                        'adSearchId' => $adSearch->id,
                    ]),
                ],
            ];
        }, $adSearches);

        $buttons[] = PaginationButtons::build($pagination, function ($page) use ($adSection) {
            return self::createRoute('index', [
                'adSection' => $adSection,
                'page' => $page,
            ]);
        });

        $rowButtons[] = [
            'callback_data' => SAdController::createRoute(),
            'text' => Emoji::BACK,
        ];

        $rowButtons[] = [
            'callback_data' => MenuController::createRoute(),
            'text' => Emoji::MENU,
        ];

        $matchesCount = AdSearchMatch::find()
            ->joinWith('adSearch')
            ->andWhere([
                AdSearch::tableName() . '.user_id' => $user->id,
                AdSearch::tableName() . '.section' => $adSection,
            ])
            ->count();

        if ($matchesCount) {
            $rowButtons[] = [
                'callback_data' => self::createRoute('section-matches', [
                    'adSection' => $adSection,
                ]),
                'text' => Emoji::OFFERS . ' ' . $matchesCount,
            ];
        }

        $rowButtons[] = [
            'callback_data' => self::createRoute('create', [
                'adSection' => $adSection,
                'm' => $this->getModelName(AdSearch::class),
            ]),
            'text' => Emoji::ADD,
        ];

        $buttons[] = $rowButtons;

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'sectionName' => AdSection::getAdSearchName($adSection),
                ]),
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
        $this->getState()->setName(self::createRoute('title-send'));
        $this->getState()->setIntermediateField('adSearchSection', $adSection);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-title'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'adSection' => $this->getState()->getIntermediateField('adSearchSection'),
                            ]),
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

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-description'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('description-skip'),
                            'text' => Yii::t('bot', 'SKIP'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('create', [
                                'm' => $this->getModelName(AdSearch::class),
                            ]),
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

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-keywords'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('keywords-skip'),
                            'text' => Yii::t('bot', 'SKIP'),
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
                $adKeyword = AdKeyword::find()
                    ->where([
                        'keyword' => $keyword,
                    ])
                    ->one();

                if (!isset($adKeyword)) {
                    $adKeyword = new AdKeyword();

                    $adKeyword->setAttributes([
                        'keyword' => $keyword,
                    ]);
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

        $pagination = new Pagination([
            'totalCount' => $currencyQuery->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $buttons = [];
        foreach ($currencyQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all() as $currency) {
            $buttons[][] = [
                'callback_data' => self::createRoute('currency-set', [
                    'currencyId' => $currency->id,
                ]),
                'text' => $currency->code . ' - ' . $currency->name,
            ];
        }

        $buttons[] = PaginationButtons::build(
            $pagination,
            function ($page) {
                return self::createRoute('keywords', [
                    'page' => $page,
                ]);
            }
        );

        $buttons[][] = [
            'callback_data' => self::createRoute('currency-skip'),
            'text' => Yii::t('bot', 'SKIP'),
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

        return $this->getResponseBuilder()
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

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-max-price', [
                    'currencyCode' => Currency::findOne($this->getState()->getIntermediateField('adSearchCurrencyId'))->code,
                ]),
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
                            'text' => Yii::t('bot', 'SKIP'),
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

        $pagination = new Pagination([
            'totalCount' => $currencyQuery->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $buttons = [];

        foreach ($currencyQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all() as $currency) {
            $buttons[][] = [
                'callback_data' => self::createRoute('currency-set', [
                    'currencyId' => $currency->id,
                ]),
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

        return $this->getResponseBuilder()
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
                'callback_data' => $this->getState()
                    ->getIntermediateField('adSearchCurrencyId') === null ? self::createRoute('keywords') : self::createRoute('currency'),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        $this->getState()->setName(self::createRoute('location-send'));

        return $this->getResponseBuilder()
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

        return $this->getResponseBuilder()
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

        $radius = min(intval($message->getText()), RadiusValidator::MAX_RADIUS);

        $this->getState()->setIntermediateField('adSearchRadius', $radius);

        return $this->actionMakeSearch();
    }

    public function actionMakeSearch()
    {
        $adSearch = new AdSearch();

        $state = $this->getState();

        $adSearch->setAttributes([
            'user_id' => $this->getTelegramUser()->id,
            'section' => intval($state->getIntermediateField('adSearchSection')),
            'title' => $state->getIntermediateField('adSearchTitle'),
            'description' => $state->getIntermediateField('adSearchDescription'),
            'pickup_radius' => doubleval($state->getIntermediateField('adSearchRadius')),
            'currency_id' => $state->getIntermediateField('adSearchCurrencyId') ? intval($state->getIntermediateField('adSearchCurrencyId')) : null,
            'max_price' => $state->getIntermediateField('adSearchMaxPrice') ? intval($state->getIntermediateField('adSearchMaxPrice')) : null,
            'location_lat' => $state->getIntermediateField('adSearchLocationLatitude'),
            'location_lon' => $state->getIntermediateField('adSearchLocationLongitude'),
            'created_at' => time(),
            'status' => AdSearch::STATUS_OFF,
        ]);

        $adSearch->save();

        foreach ($this->getState()->getIntermediateFieldArray('adSearchKeywords') as $adKeywordId) {
            $adKeyword = AdKeyword::findOne($adKeywordId);

            $adSearch->link('keywords', $adKeyword);
        }

        return $this->actionView($adSearch->id);
    }

    public function actionView($adSearchId)
    {
        $this->getState()->setName(null);
        $user = $this->getUser();

        $adSearch = $user->getAdSearches()
            ->where([
                'user_id' => $user->id,
                'id' => $adSearchId,
            ])
            ->one();

        if (!isset($adSearch)) {
            return [];
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('status', [
                    'adSearchId' => $adSearch->id,
                ]),
                'text' => Yii::t('bot', 'Status') . ': ' . ($adSearch->isActive() ? 'ON' : 'OFF'),
            ]
        ];

        $matchesCount = $adSearch->getMatches()->count();

        if ($matchesCount) {
            $buttons[][] = [
                'callback_data' => self::createRoute('matches', [
                    'adSearchId' => $adSearch->id,
                ]),
                'text' => Emoji::OFFERS . ' ' . $matchesCount,
            ];
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('index', [
                    'adSection' => $adSearch->section,
                ]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'callback_data' => self::createRoute('u', [
                    'm' => $this->getModelName(AdSearch::class),
                    'i' => $adSearch->id,
                ]),
                'text' => Emoji::EDIT,
            ],
            [
                'callback_data' => self::createRoute('delete', [
                    'adSearchId' => $adSearch->id,
                ]),
                'text' => Emoji::DELETE,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('show', [
                    'sectionName' => AdSection::getAdSearchName($adSearch->section),
                    'keywords' => self::getKeywordsAsString($adSearch->getKeywords()->all()),
                    'adSearch' => $adSearch,
                    'currency' => isset($adSearch->currency_id) ? Currency::findOne($adSearch->currency_id) : null,
                    'locationLink' => ExternalLink::getOSMLink($adSearch->location_lat, $adSearch->location_lon),
                    'showDetailedInfo' => true,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    public function actionEdit($adSearchId)
    {
        $this->getState()->setName(null);
        $user = $this->getUser();

        $adSearch = $user->getAdSearches()
            ->where([
                'id' => $adSearchId,
            ])
            ->one();

        if (!isset($adSearch)) {
            return [];
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render( 'show', [
                    'adSearch' => $adSearch,
                    'currency' => Currency::findOne($adSearch->currency_id),
                    'sectionName' => AdSection::getAdSearchName($adSearch->section),
                    'keywords' => self::getKeywordsAsString($adSearch->getKeywords()->all()),
                    'locationLink' => ExternalLink::getOSMLink($adSearch->location_lat, $adSearch->location_lon),
                    'showDetailedInfo' => false,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit-title', [
                                'adSearchId' => $adSearchId,
                            ]),
                            'text' => Yii::t('bot', 'Title'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-description', [
                                'adSearchId' => $adSearchId,
                            ]),
                            'text' => Yii::t('bot', 'Description'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-keywords', [
                                'adSearchId' => $adSearchId,
                            ]),
                            'text' => Yii::t('bot', 'Keywords'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-max-price', [
                                'adSearchId' => $adSearchId,
                            ]),
                            'text' => Yii::t('bot', 'Max price'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-location', [
                                'adSearchId' => $adSearchId,
                            ]),
                            'text' => Yii::t('bot', 'Location'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-radius', [
                                'adSearchId' => $adSearchId,
                            ]),
                            'text' => Yii::t('bot', 'Pickup radius'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('view', [
                                'adSearchId' => $adSearchId,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    public function actionEditTitle($adSearchId)
    {
        $this->getState()->setName(
            self::createRoute('new-title', [
                'adSearchId' => $adSearchId,
            ])
        );

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-title'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('u', [
                                'm' => $this->getModelName(AdSearch::class),
                                'i' => $adSearchId,
                            ]),
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

            $adSearch->setAttributes([
                'title' => $message->getText(),
            ]);

            $adSearch->save();

            return $this->actionView($adSearchId);
        } else {
            return $this->actionEditTitle($adSearchId);
        }
    }

    public function actionEditDescription($adSearchId)
    {
        $this->getState()->setName(
            self::createRoute('new-description', [
                'adSearchId' => $adSearchId,
            ])
        );

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-description'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('new-description-skip', [
                                'adSearchId' => $adSearchId,
                            ]),
                            'text' => Yii::t('bot', 'NO'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('u', [
                                'm' => $this->getModelName(AdSearch::class),
                                'i' => $adSearchId,
                            ]),
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

        $adSearch->setAttributes([
            'description' => null,
        ]);

        $adSearch->save();

        return $this->actionView($adSearchId);
    }

    public function actionNewDescription($adSearchId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            $adSearch = AdSearch::findOne($adSearchId);

            $adSearch->setAttributes([
                'description' => $message->getText(),
            ]);

            $adSearch->save();

            return $this->actionView($adSearchId);
        } else {
            return $this->actionEditDescription($adSearchId);
        }
    }

    public function actionEditCurrency($adSearchId, $page = 1)
    {
        $adSearch = AdSearch::findOne($adSearchId);

        $currencyQuery = Currency::find();

        $pagination = new Pagination([
            'totalCount' => $currencyQuery->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $buttons = [];

        $telegramUser = $this->getTelegramUser();
        $userCurrencyId = null;
        if ($telegramUser->user_id && User::findOne($telegramUser->user_id)) {
            $user = User::findOne($telegramUser->user_id);

            if ($user->currency_id !== null) {
                $userCurrencyId = $user->currency_id;

                $buttons[][] = [
                    'callback_data' => self::createRoute('edit-currency-set', [
                        'adSearchId' => $adSearchId,
                        'currencyId' => $user->currency_id,
                    ]),
                    'text' => '· ' . Currency::findOne($user->currency_id)->code . ' - ' . Currency::findOne($user->currency_id)->name,
                ];
            }
        }

        foreach ($currencyQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all() as $currency) {
            $buttons[][] = [
                'callback_data' => self::createRoute('edit-currency-set', [
                    'adSearchId' => $adSearchId,
                    'currencyId' => $currency->id,
                ]),
                'text' => $currency->code . ' - ' . $currency->name,
            ];
        }

        $buttons[] = PaginationButtons::build(
            $pagination,
            function ($page) use ($adSearchId) {
                return self::createRoute('edit-currency', [
                    'adSearchId' => $adSearchId,
                    'page' => $page,
                ]);
            }
        );

        $buttons[] = [
            [
                'callback_data' => isset($adSearch->currency_id)
                    ? self::createRoute('edit-max-price', [
                        'adSearchId' => $adSearchId,
                    ])
                    : self::createRoute('u', [
                        'm' => $this->getModelName(AdSearch::class),
                        'i' => $adSearchId,
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
                $this->render('edit-currency'),
                $buttons
            )
            ->build();
    }

    public function actionEditCurrencySet($adSearchId, $currencyId)
    {
        $adSearch = AdSearch::findOne($adSearchId);

        $adSearch->setAttributes([
            'currency_id' => $currencyId,
        ]);

        $adSearch->save();

        return $this->actionEditMaxPrice($adSearchId);
    }

    public function actionEditMaxPrice($adSearchId)
    {
        $adSearch = AdSearch::findOne($adSearchId);

        if (!isset($adSearch->currency_id)) {
            return $this->actionEditCurrency($adSearchId);
        }

        $this->getState()->setName(self::createRoute('edit-max-price-set', [
            'adSearchId' => $adSearchId,
        ]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-max-price', [
                    'currencyCode' => Currency::findOne($adSearch->currency_id)->code,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit-currency', [
                                'adSearchId' => $adSearchId,
                            ]),
                            'text' => Yii::t('bot', 'Edit currency'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('u', [
                                'm' => $this->getModelName(AdSearch::class),
                                'i' => $adSearchId,
                            ]),
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

            $adSearch->setAttributes([
                'max_price' => $maxPrice,
            ]);

            $adSearch->save();

            return $this->actionView($adSearchId);
        }
    }

    public function actionEditKeywords($adSearchId)
    {
        $this->getState()->setName(self::createRoute('new-keywords', ['adSearchId' => $adSearchId]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-keywords'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('new-keywords-skip', [
                                'adSearchId' => $adSearchId,
                            ]),
                            'text' => Yii::t('bot', 'NO'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('u', [
                                'm' => $this->getModelName(AdSearch::class),
                                'i' => $adSearchId,
                            ]),
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

        return $this->actionView($adSearchId);
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
                $adKeyword = AdKeyword::find()
                    ->where([
                        'keyword' => $keyword,
                    ])
                    ->one();

                if (!isset($adKeyword)) {
                    $adKeyword = new AdKeyword();

                    $adKeyword->setAttributes([
                        'keyword' => $keyword,
                    ]);

                    $adKeyword->save();
                }

                $adSearch->link('keywords', $adKeyword);
            }

            $adSearch->markToUpdateMatches();

            return $this->actionView($adSearchId);
        }
    }

    public function actionEditLocation($adSearchId)
    {
        $this->getState()->setName(self::createRoute('new-location-send', [
            'adSearchId' => $adSearchId,
        ]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-location'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('new-location-my', [
                                'adSearchId' => $adSearchId,
                            ]),
                            'text' => Yii::t('bot', 'My location'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('u', [
                                'm' => $this->getModelName(AdSearch::class),
                                'i' => $adSearchId,
                            ]),
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

            $adSearch->setAttributes([
                'location_lat' => strval($latitude),
                'location_lon' => strval($longitude),
            ]);

            $adSearch->save();

            return $this->actionView($adSearchId);
        } else {
            return $this->actionEditLocation($adSearchId);
        }
    }

    public function actionEditRadius($adSearchId)
    {
        $this->getState()->setName(self::createRoute('new-radius', [
            'adSearchId' => $adSearchId,
        ]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-radius'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('new-radius-skip', [
                                'adSearchId' => $adSearchId,
                            ]),
                            'text' => Yii::t('bot', 'No pickup'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('u', [
                                'm' => $this->getModelName(AdSearch::class),
                                'i' => $adSearchId,
                            ]),
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
            $adSearch->setAttributes([
                'pickup_radius' => 0,
            ]);

            $adSearch->save();
        }

        return $this->actionView($adSearchId);
    }

    public function actionNewRadius($adSearchId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            if (!AdOffer::validateRadius($message->getText())) {
                return $this->actionEditRadius($adSearchId);
            }

            $radius = min(intval($message->getText()), RadiusValidator::MAX_RADIUS);

            $adSearch = AdSearch::findOne($adSearchId);

            $adSearch->setAttributes([
                'pickup_radius' => $radius,
            ]);

            $adSearch->save();

            $adSearch->markToUpdateMatches();

            return $this->actionView($adSearchId);
        }
    }

    public function actionStatus($adSearchId)
    {
        $adSearch = AdSearch::findOne($adSearchId);

        $adSearch->setAttributes([
            'status' => ($adSearch->isActive() ? AdSearch::STATUS_OFF : AdSearch::STATUS_ON),
        ]);

        $adSearch->save();

        if ($adSearch->isActive()) {
            $adSearch->markToUpdateMatches();
        } else {
            $adSearch->unlinkAll('matches', true);
            $adSearch->unlinkAll('counterMatches', true);

            $adSearch->setAttributes([
                'processed_at' => time(),
            ]);

            $adSearch->save();
        }

        return $this->actionView($adSearchId);
    }

    public function actionMatches($adSearchId, $page = 1)
    {
        $user = $this->getUser();

        $adSearch = $user->getAdSearches()
            ->where([
                'user_id' => $user->id,
                'id' => $adSearchId,
            ])
            ->one();

        if (!isset($adSearch)) {
            return [];
        }

        $matchesQuery = $adSearch->getMatches();
        $matchesCount = $matchesQuery->count();

        if (!$matchesCount) {
            return $this->actionView($adSearch->id);
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

        $adOffer = $matchesQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->one();

        $buttons[] = [
            [
                'text' => $adSearch->title,
                'callback_data' => self::createRoute('view', [
                    'adSearchId' => $adSearch->id,
                ]),
            ]
        ];

        $buttons[] = PaginationButtons::build($pagination, function ($page) use ($adSearchId) {
            return self::createRoute('matches', [
                'adSearchId' => $adSearchId,
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'adSearchId' => $adSearch->id,
                ]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        return $this->getResponseBuilder()
            ->sendPhotoOrEditMessageTextOrSendMessage(
                $adOffer->getPhotos()->count() ? $adOffer->getPhotos()->one()->file_id : null,
                $this->render('match', [
                    'adOffer' => $adOffer,
                    'user' => TelegramUser::findOne(['user_id' => $adOffer->user_id]),
                    'currency' => Currency::findOne($adOffer->currency_id),
                    'sectionName' => AdSection::getAdOfferName($adOffer->section),
                    'keywords' => self::getKeywordsAsString($adOffer->getKeywords()->all()),
                    'locationLink' => ExternalLink::getOSMLink($adOffer->location_lat, $adOffer->location_lon),
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    public function actionSectionMatches($adSection, $page = 1)
    {
        $user = $this->getUser();

        $matchesQuery = AdSearchMatch::find()
            ->joinWith('adSearch')
            ->andWhere([
                AdSearch::tableName() . '.user_id' => $user->id,
                AdSearch::tableName() . '.section' => $adSection,
            ]);

        $matchesCount = $matchesQuery->count();

        if (!$matchesCount) {
            return $this->actionIndex($adSection);
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

        $adSearchMatch = $matchesQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->one();
        $adSearch = $adSearchMatch->adSearch;
        $adOffer = $adSearchMatch->adOffer;

        $buttons[] = [
            [
                'text' => $adSearch->title,
                'callback_data' => self::createRoute('view', [
                    'adSearchId' => $adSearch->id,
                ]),
            ]
        ];

        $buttons[] = PaginationButtons::build($pagination, function ($page) use ($adSection) {
            return self::createRoute('section-matches', [
                'adSection' => $adSection,
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'callback_data' => self::createRoute('index', [
                    'adSection' => $adSection,
                ]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        return $this->getResponseBuilder()
            ->sendPhotoOrEditMessageTextOrSendMessage(
                $adOffer->getPhotos()->count() ? $adOffer->getPhotos()->one()->file_id : null,
                $this->render('match', [
                    'adOffer' => $adOffer,
                    'user' => TelegramUser::findOne(['user_id' => $adOffer->user_id]),
                    'currency' => Currency::findOne($adOffer->currency_id),
                    'sectionName' => AdSection::getAdOfferName($adOffer->section),
                    'keywords' => self::getKeywordsAsString($adOffer->getKeywords()->all()),
                    'locationLink' => ExternalLink::getOSMLink($adOffer->location_lat, $adOffer->location_lon),
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    public function actionDelete($adSearchId)
    {
        $user = $this->getUser();

        $adSearch = $user->getAdSearches()
            ->where([
                'id' => $adSearchId,
            ])
            ->one();

        if (!isset($adSearch)) {
            return [];
        }

        $adSection = $adSearch->section;

        $adSearch->unlinkAll('keywords', true);
        $adSearch->delete();

        return $this->actionIndex($adSection);
    }
}
