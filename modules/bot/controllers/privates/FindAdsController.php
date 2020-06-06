<?php
namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\models\AdCategory;
use app\modules\bot\models\AdKeyword;
use app\modules\bot\models\AdOrder;
use app\modules\bot\models\AdSearch;
use app\modules\bot\models\AdPhoto;
use yii\data\Pagination;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\User as TelegramUser;
use app\models\User;
use app\models\Currency;

class FindAdsController extends Controller
{
    public function actionIndex($adCategoryId, $page = 1)
    {
        $this->getState()->setName(null);

        $buttons = [];

        $adSearchQuery = AdSearch::find()->where([
            'user_id' => $this->getTelegramUser()->id,
            'category_id' => $adCategoryId,
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

        foreach ($adSearchQuery
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->all() as $adSearch) {
            $buttons[][] = [
                'callback_data' => self::createRoute('search', ['adSearchId' => $adSearch->id]),
                'text' => ($adSearch->isActive() ? '' : '❌ ') . self::getKeywordsAsString($adSearch->getKeywords()->all()),
            ];
        }

        $buttons[] = PaginationButtons::build($pagination, function ($page) use ($adCategoryId) {
            return self::createRoute('index', [
                'adCategoryId' => $adCategoryId,
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'callback_data' => AdsController::createRoute(),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'callback_data' => self::createRoute('add', ['adCategoryId' => $adCategoryId]),
                'text' => Emoji::ADD,
            ],
        ];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'categoryName' => AdCategory::getFindName($adCategoryId),
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

    public function actionAdd($adCategoryId)
    {
        $this->getState()->setIntermediateField('findAdCategoryId', $adCategoryId);

        $this->getState()->setName(self::createRoute('keywords'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-keywords'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'adCategoryId' => $this->getState()->getIntermediateField('findAdCategoryId'),
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

    public function actionKeywords($page = 1)
    {
        if ($message = $this->getUpdate()->getMessage()) {
            $keywords = PlaceAdController::parseKeywords($message->getText());

            if (empty($keywords)) {
                return $this->actionAdd($this->getState()->getIntermediateField('findAdCategoryId'));
            }

            $findAdKeywords = [];

            foreach ($keywords as $keyword) {
                $adKeyword = AdKeyword::find()->where([
                    'keyword' => $keyword,
                ])->one();

                if (!isset($adKeyword)) {
                    $adKeyword = new AdKeyword();

                    $adKeyword->setAttributes([
                        'keyword' => $keyword,
                    ]);
                    $adKeyword->save();
                }

                $findAdKeywords[] = $adKeyword->id;
            }
            $this->getState()->setIntermediateFieldArray('findAdKeywords', $findAdKeywords);
        }

        $this->getState()->setName(null);

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
        if ($telegramUser->user_id && User::findOne($telegramUser->user_id)) {
            $user = User::findOne($telegramUser->user_id);

            if ($user->currency_id !== null) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('currency-set', ['currencyId' => $user->currency_id]),
                    'text' => '· ' . Currency::findOne($user->currency_id)->code . ' - ' . Currency::findOne($user->currency_id)->name . ' ·',
                ];
            }
        }

        foreach ($currencyQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all() as $currency) {
            $buttons[][] = [
                'callback_data' => self::createRoute('currency-set', ['currencyId' => $currency->id]),
                'text' => $currency->code . ' - ' . $currency->name,
            ];
        }

        $buttons[] = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('keywords', ['page' => $page]);
        });

        $buttons[][] = [
            'callback_data' => self::createRoute('currency-skip'),
            'text' => Yii::t('bot', 'Skip'),
        ];

        $buttons[] = [
            [
                'callback_data' => self::createRoute('add', [
                    'adCategoryId' => $this->getState()->getIntermediateField('findAdCategoryId'),
                ]),
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
        $this->getState()->setIntermediateField('findAdCurrencyId', $currencyId);

        return $this->actionCurrency();
    }

    public function actionCurrency()
    {
        $this->getState()->setName(self::createRoute('max-price-send'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-max-price', [
                    'currencyCode' => Currency::findOne($this->getState()->getIntermediateField('findAdCurrencyId'))->code,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('keywords'),
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

    public function actionCurrencySkip()
    {
        $this->getState()->setIntermediateField('findAdCurrencyId', null);

        return $this->actionMaxPriceSkip();
    }

    public function actionMaxPriceSend()
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            if (!AdOrder::validatePrice($message->getText())) {
                return $this->actionKeywords();
            }

            $maxPrice = $message->getText();

            $this->getState()->setIntermediateField('findAdMaxPrice', $maxPrice);

            return $this->actionMaxPrice();
        }
    }

    public function actionMaxPriceSkip()
    {
        $this->getState()->setIntermediateField('findAdMaxPrice', null);

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
                'callback_data' => $this->getState()->getIntermediateField('findAdCurrencyId') === null ? self::createRoute('keywords') : self::createRoute('currency'),
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
        } elseif ($message && $message->getText() && AdOrder::validateLocation($message->getText())) {
            $latitude = AdOrder::getLatitudeFromText($message->getText());
            $longitude = AdOrder::getLongitudeFromText($message->getText());
        } else {
            $latitude = null;
            $longitude = null;
        }

        return $this->actionLocationSet($latitude, $longitude);
    }

    public function actionLocationSet($latitude, $longitude)
    {
        if ($latitude && $longitude) {
            $this->getState()->setIntermediateField('findAdLocationLatitude', strval($latitude));
            $this->getState()->setIntermediateField('findAdLocationLongitude', strval($longitude));

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

    public function actionRadius()
    {
        $radius = $this->getUpdate()->getMessage()->getText();

        if (!AdOrder::validateRadius($radius)) {
            return $this->actionLocation();
        }

        $this->getState()->setIntermediateField('findAdRadius', $radius);

        return $this->actionMakeSearch();
    }

    public function actionMakeSearch()
    {
        $adSearch = new AdSearch();

        $state = $this->getState();

        $adSearch->setAttributes([
            'user_id' => $this->getTelegramUser()->id,
            'category_id' => intval($state->getIntermediateField('findAdCategoryId')),
            'pickup_radius' => doubleval($state->getIntermediateField('findAdRadius')),
            'currency_id' => $state->getIntermediateField('findAdCurrencyId') ? intval($state->getIntermediateField('findAdCurrencyId')) : null,
            'max_price' => $state->getIntermediateField('findAdMaxPrice') ? intval($state->getIntermediateField('findAdMaxPrice')) : null,
            'location_latitude' => $state->getIntermediateField('findAdLocationLatitude'),
            'location_longitude' => $state->getIntermediateField('findAdLocationLongitude'),
            'created_at' => time(),
            'renewed_at' => time(),
            'status' => AdSearch::STATUS_OFF,
            'edited_at' => time(),
        ]);

        $adSearch->save();

        foreach ($this->getState()->getIntermediateFieldArray('findAdKeywords') as $adKeywordId) {
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

        $matchedAdOrderCount = $adSearch->getMatches()->count();

        if ($matchedAdOrderCount > 0) {
            $buttons[][] = [
                'callback_data' => self::createRoute('ad-order-matches', ['adSearchId' => $adSearchId]),
                'text' => '🙋‍♂️ ' . $matchedAdOrderCount,
            ];
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('index', ['adCategoryId' => $adSearch->category_id]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'callback_data' => self::createRoute('edit', ['adSearchId' => $adSearchId]),
                'text' => Emoji::EDIT,
            ],
            [
                'callback_data' => self::createRoute('confirm-delete', ['adSearchId' => $adSearchId]),
                'text' => Emoji::DELETE,
            ],
        ];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('search', [
                    'categoryName' => AdCategory::getFindName($adSearch->category_id),
                    'keywords' => self::getKeywordsAsString($adSearch->getKeywords()->all()),
                    'adSearch' => $adSearch,
                    'currency' => isset($adSearch->currency_id) ? Currency::findOne($adSearch->currency_id) : null,
                    'locationLink' => ExternalLink::getOSMLink($adSearch->location_latitude, $adSearch->location_longitude),
                    'liveDays' => AdSearch::LIVE_DAYS,
                    'showDetailedInfo' => true,
                ]),
                $buttons,
                true
            )
            ->build();
    }

    public function updateSearch($adSearchId)
    {
        $adSearch = AdSearch::findOne($adSearchId);

        $adSearch->setAttributes([
            'renewed_at' => time(),
        ]);
        $adSearch->save();
    }

    public function actionEdit($adSearchId)
    {
        $this->getState()->setName(null);

        $adSearch = AdSearch::findOne($adSearchId);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('search', [
                    'categoryName' => AdCategory::getFindName($adSearch->category_id),
                    'keywords' => self::getKeywordsAsString($adSearch->getKeywords()->all()),
                    'adSearch' => $adSearch,
                    'currency' => isset($adSearch->currency_id) ? Currency::findOne($adSearch->currency_id) : null,
                    'locationLink' => ExternalLink::getOSMLink($adSearch->location_latitude, $adSearch->location_longitude),
                    'liveDays' => AdSearch::LIVE_DAYS,
                    'showDetailedInfo' => false,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit-keywords', ['adSearchId' => $adSearchId]),
                            'text' => Yii::t('bot', 'Keywords'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-max-price', ['adSearchId' => $adSearchId]),
                            'text' => Yii::t('bot', 'Max price'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-location', ['adSearchId' => $adSearchId]),
                            'text' => Yii::t('bot', 'Location'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-radius', ['adSearchId' => $adSearchId]),
                            'text' => Yii::t('bot', 'Pickup radius'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('search', ['adSearchId' => $adSearchId]),
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
                        'currencyId' => $user->currency_id]),
                    'text' => '· ' . Currency::findOne($user->currency_id)->code . ' - ' . Currency::findOne($user->currency_id)->name . ' ·',
                ];
            }
        }

        foreach ($currencyQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all() as $currency) {
            $buttons[][] = [
                'callback_data' => self::createRoute('edit-currency-set', [
                    'adSearchId' => $adSearchId,
                    'currencyId' => $currency->id
                ]),
                'text' => $currency->code . ' - ' . $currency->name,
            ];
        }

        $buttons[] = PaginationButtons::build($pagination, function ($page) use ($adSearchId) {
            return self::createRoute('edit-currency', [
                'adSearchId' => $adSearchId,
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'callback_data' => isset($adSearch->currency_id)
                    ? self::createRoute('edit-max-price', ['adSearchId' => $adSearchId])
                    : self::createRoute('edit', ['adSearchId' => $adSearchId]),
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

        $this->getState()->setName(self::createRoute('edit-max-price-set', ['adSearchId' => $adSearchId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-max-price', [
                    'currencyCode' => Currency::findOne($adSearch->currency_id)->code,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit-currency', ['adSearchId' => $adSearchId]),
                            'text' => Yii::t('bot', 'Edit currency'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit', ['adSearchId' => $adSearchId]),
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
            if (!AdOrder::validatePrice($message->getText())) {
                return $this->actionEditMaxPrice($adSearchId);
            }

            $maxPrice = $message->getText();

            $adSearch = AdSearch::findOne($adSearchId);

            $adSearch->setAttributes([
                'max_price' => $maxPrice,
            ]);
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
                            'callback_data' => self::createRoute('edit', ['adSearchId' => $adSearchId]),
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

    public function actionNewKeywords($adSearchId)
    {
        if ($message = $this->getUpdate()->getMessage()) {
            $keywords = PlaceAdController::parseKeywords($message->getText());

            if (empty($keywords)) {
                return $this->actionEditKeywords($adSearchId);
            }

            $adSearch = AdSearch::findOne($adSearchId);

            $adSearch->unlinkAll('keywords', true);
            foreach ($keywords as $keyword) {
                $adKeyword = AdKeyword::find()->where([
                    'keyword' => $keyword,
                ])->one();

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
                            'callback_data' => self::createRoute('new-location-my', ['adSearchId' => $adSearchId]),
                            'text' => Yii::t('bot', 'My location'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit', ['adSearchId' => $adSearchId]),
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
        } elseif ($message && $message->getText() && AdOrder::validateLocation($message->getText())) {
            $latitude = AdOrder::getLatitudeFromText($message->getText());
            $longitude = AdOrder::getLongitudeFromText($message->getText());
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
                'location_latitude' => strval($latitude),
                'location_longitude' => strval($longitude),
            ]);
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
                            'callback_data' => self::createRoute('edit', ['adSearchId' => $adSearchId]),
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

    public function actionNewRadius($adSearchId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            if (!AdOrder::validateRadius($message->getText())) {
                return $this->actionEditRadius($adSearchId);
            }

            $radius = $message->getText();

            $adSearch = AdSearch::findOne($adSearchId);

            $adSearch->setAttributes([
                'pickup_radius' => $radius,
            ]);
            $adSearch->save();
            $adSearch->markToUpdateMatches();

            return $this->actionSearch($adSearchId);
        }
    }

    public function actionStatus($adSearchId)
    {
        $adSearch = AdSearch::findOne($adSearchId);

        $adSearch->setAttributes([
            'status' => ($adSearch->isActive() ? AdSearch::STATUS_NOT_ACTIVATED : AdSearch::STATUS_ACTIVATED),
        ]);
        $adSearch->save();

        if ($adSearch->isActive()) {
            $adSearch->markToUpdateMatches();
        } else {
            $adSearch->unlinkAll('matches', true);
            $adSearch->setAttributes([
                'edited_at' => null,
            ]);
            $adSearch->save();
        }

        return $this->actionSearch($adSearchId);
    }

    public function actionAdOrderMatches($adSearchId, $page = 1)
    {
        $adSearch = AdSearch::findOne($adSearchId);

        $adOrderQuery = $adSearch->getMatches();

        if ($adOrderQuery->count() == 0) {
            return $this->actionSearch($adSearchId);
        }

        $pagination = new Pagination([
            'totalCount' => $adOrderQuery->count(),
            'pageSize' => 1,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($adSearchId) {
            return self::createRoute('ad-order-matches', [
                'adSearchId' => $adSearchId,
                'page' => $page,
            ]);
        });

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

        $adOrder = $adOrderQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all()[0];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->sendPhotoOrEditMessageTextOrSendMessage(
                $adOrder->getPhotos()->count() ? $adOrder->getPhotos()->one()->file_id : null,
                $this->render('ad-order-matches', [
                    'adOrder' => $adOrder,
                    'user' => TelegramUser::findOne($adOrder->user_id),
                    'currency' => Currency::findOne($adOrder->currency_id),
                    'categoryName' => AdCategory::getPlaceName($adOrder->category_id),
                    'keywords' => self::getKeywordsAsString($adOrder->getKeywords()->all()),
                    'locationLink' => ExternalLink::getOSMLink($adOrder->location_latitude, $adOrder->location_longitude),
                ]),
                $buttons,
                true
            )
            ->build();
    }

    public function actionConfirmDelete($adSearchId)
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('confirm-delete'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('search', ['adSearchId' => $adSearchId]),
                            'text' => '❌',
                        ],
                        [
                            'callback_data' => self::createRoute('delete', ['adSearchId' => $adSearchId]),
                            'text' => '✅',
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionDelete($adSearchId)
    {
        $adSearch = AdSearch::findOne($adSearchId);

        if (isset($adSearch)) {
            $adCategoryId = $adSearch->category_id;

            $adSearch->unlinkAll('keywords', true);

            $adSearch->delete();

            return $this->actionIndex($adCategoryId);
        }
    }
}
