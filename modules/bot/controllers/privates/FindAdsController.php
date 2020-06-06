<?php
namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\models\AdCategory;
use app\modules\bot\models\AdKeyword;
use app\modules\bot\models\AdsPost;
use app\modules\bot\models\AdsPostSearch;
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

        $adsPostSearchQuery = AdsPostSearch::find()->where([
            'user_id' => $this->getTelegramUser()->id,
            'category_id' => $adCategoryId,
        ]);

        $adsPostSearchCount = $adsPostSearchQuery->count();

        $pagination = new Pagination([
            'totalCount' => $adsPostSearchCount,
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        foreach ($adsPostSearchQuery
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->all() as $adsPostSearch) {
            $buttons[][] = [
                'callback_data' => self::createRoute('search', ['adsPostSearchId' => $adsPostSearch->id]),
                'text' => ($adsPostSearch->isActive() ? '' : '❌ ') . self::getKeywordsAsString($adsPostSearch->getKeywords()->all()),
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
            $keywords[] = $adKeyword->word;
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

            foreach ($keywords as $index => $word) {
                $adKeyword = AdKeyword::find()->where([
                    'word' => $word,
                ])->one();

                if (!isset($adKeyword)) {
                    $adKeyword = new AdKeyword();

                    $adKeyword->setAttributes([
                        'word' => $word,
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
            if (!AdsPost::validatePrice($message->getText())) {
                return $this->actionKeywords();
            }

            $maxPrice = intval(doubleval($message->getText()) * 100);

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
        } elseif ($message && $message->getText() && AdsPost::validateLocation($message->getText())) {
            $latitude = AdsPost::getLatitudeFromText($message->getText());
            $longitude = AdsPost::getLongitudeFromText($message->getText());
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

        if (!AdsPost::validateRadius($radius)) {
            return $this->actionLocation();
        }

        $this->getState()->setIntermediateField('findAdRadius', $radius);

        return $this->actionMakeSearch();
    }

    public function actionMakeSearch()
    {
        $adsPostSearch = new AdsPostSearch();

        $state = $this->getState();
        $user = $this->getTelegramUser();

        $adsPostSearch->setAttributes([
            'user_id' => $user->id,
            'category_id' => intval($state->getIntermediateField('findAdCategoryId')),
            'radius' => intval($state->getIntermediateField('findAdRadius')),
            'currency_id' => $state->getIntermediateField('findAdCurrencyId') ? intval($state->getIntermediateField('findAdCurrencyId')) : null,
            'max_price' => $state->getIntermediateField('findAdMaxPrice') ? intval($state->getIntermediateField('findAdMaxPrice')) : null,
            'location_latitude' => $state->getIntermediateField('findAdLocationLatitude'),
            'location_longitude' => $state->getIntermediateField('findAdLocationLongitude'),
            'updated_at' => time(),
            'status' => AdsPostSearch::STATUS_NOT_ACTIVATED,
            'edited_at' => time(),
        ]);

        $adsPostSearch->save();

        foreach ($this->getState()->getIntermediateFieldArray('findAdKeywords') as $adKeywordId) {
            $adKeyword = AdKeyword::findOne($adKeywordId);

            $adsPostSearch->link('keywords', $adKeyword);
        }

        return $this->actionSearch($adsPostSearch->id);
    }

    public function actionSearch($adsPostSearchId)
    {
        $this->updateSearch($adsPostSearchId);

        $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

        $buttons = [];

        $buttons[][] = [
            'callback_data' => self::createRoute('status', ['adsPostSearchId' => $adsPostSearchId]),
            'text' => 'Status: ' . ($adsPostSearch->isActive() ? 'ON' : 'OFF'),
        ];

        $matchedPostsCount = $adsPostSearch->getMatches()->count();

        if ($matchedPostsCount > 0) {
            $buttons[][] = [
                'callback_data' => self::createRoute('post-matches', ['adsPostSearchId' => $adsPostSearchId]),
                'text' => '🙋‍♂️ ' . $matchedPostsCount,
            ];
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('index', ['adCategoryId' => $adsPostSearch->category_id]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'callback_data' => self::createRoute('edit', ['adsPostSearchId' => $adsPostSearchId]),
                'text' => Emoji::EDIT,
            ],
            [
                'callback_data' => self::createRoute('confirm-delete', ['adsPostSearchId' => $adsPostSearchId]),
                'text' => Emoji::DELETE,
            ],
        ];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('search', [
                    'categoryName' => AdCategory::getFindName($adsPostSearch->category_id),
                    'keywords' => self::getKeywordsAsString($adsPostSearch->getKeywords()->all()),
                    'adsPostSearch' => $adsPostSearch,
                    'currency' => isset($adsPostSearch->currency_id) ? Currency::findOne($adsPostSearch->currency_id) : null,
                    'locationLink' => ExternalLink::getOSMLink($adsPostSearch->location_latitude, $adsPostSearch->location_longitude),
                    'liveDays' => AdsPostSearch::LIVE_DAYS,
                    'showDetailedInfo' => true,
                ]),
                $buttons,
                true
            )
            ->build();
    }

    public function updateSearch($adsPostSearchId)
    {
        $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

        $adsPostSearch->setAttributes([
            'updated_at' => time(),
        ]);
        $adsPostSearch->save();
    }

    public function actionEdit($adsPostSearchId)
    {
        $this->getState()->setName(null);

        $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('search', [
                    'categoryName' => AdCategory::getFindName($adsPostSearch->category_id),
                    'keywords' => self::getKeywordsAsString($adsPostSearch->getKeywords()->all()),
                    'adsPostSearch' => $adsPostSearch,
                    'currency' => isset($adsPostSearch->currency_id) ? Currency::findOne($adsPostSearch->currency_id) : null,
                    'locationLink' => ExternalLink::getOSMLink($adsPostSearch->location_latitude, $adsPostSearch->location_longitude),
                    'liveDays' => AdsPostSearch::LIVE_DAYS,
                    'showDetailedInfo' => false,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit-keywords', ['adsPostSearchId' => $adsPostSearchId]),
                            'text' => Yii::t('bot', 'Keywords'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-max-price', ['adsPostSearchId' => $adsPostSearchId]),
                            'text' => Yii::t('bot', 'Max price'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-location', ['adsPostSearchId' => $adsPostSearchId]),
                            'text' => Yii::t('bot', 'Location'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-radius', ['adsPostSearchId' => $adsPostSearchId]),
                            'text' => Yii::t('bot', 'Pickup radius'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('search', ['adsPostSearchId' => $adsPostSearchId]),
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

    public function actionEditCurrency($adsPostSearchId, $page = 1)
    {
        $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

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
                        'adsPostSearchId' => $adsPostSearchId,
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
                    'adsPostSearchId' => $adsPostSearchId,
                    'currencyId' => $currency->id
                ]),
                'text' => $currency->code . ' - ' . $currency->name,
            ];
        }

        $buttons[] = PaginationButtons::build($pagination, function ($page) use ($adsPostSearchId) {
            return self::createRoute('edit-currency', [
                'adsPostSearchId' => $adsPostSearchId,
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'callback_data' => isset($adsPostSearch->currency_id)
                    ? self::createRoute('edit-max-price', ['adsPostSearchId' => $adsPostSearchId])
                    : self::createRoute('edit', ['adsPostSearchId' => $adsPostSearchId]),
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

    public function actionEditCurrencySet($adsPostSearchId, $currencyId)
    {
        $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

        $adsPostSearch->setAttributes([
            'currency_id' => $currencyId,
        ]);

        $adsPostSearch->save();

        return $this->actionEditMaxPrice($adsPostSearchId);
    }

    public function actionEditMaxPrice($adsPostSearchId)
    {
        $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

        if (!isset($adsPostSearch->currency_id)) {
            return $this->actionEditCurrency($adsPostSearchId);
        }

        $this->getState()->setName(self::createRoute('edit-max-price-set', ['adsPostSearchId' => $adsPostSearchId]));

        
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-max-price', [
                    'currencyCode' => Currency::findOne($adsPostSearch->currency_id)->code,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit-currency', ['adsPostSearchId' => $adsPostSearchId]),
                            'text' => Yii::t('bot', 'Edit currency'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit', ['adsPostSearchId' => $adsPostSearchId]),
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

    public function actionEditMaxPriceSet($adsPostSearchId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            if (!AdsPost::validatePrice($message->getText())) {
                return $this->actionEditMaxPrice($adsPostSearchId);
            }

            $maxPrice = intval(doubleval($message->getText()) * 100);

            $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

            $adsPostSearch->setAttributes([
                'max_price' => $maxPrice,
            ]);
            $adsPostSearch->save();

            return $this->actionSearch($adsPostSearchId);
        }
    }

    public function actionEditKeywords($adsPostSearchId)
    {
        $this->getState()->setName(self::createRoute('new-keywords', ['adsPostSearchId' => $adsPostSearchId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-keywords'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit', ['adsPostSearchId' => $adsPostSearchId]),
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

    public function actionNewKeywords($adsPostSearchId)
    {
        if ($message = $this->getUpdate()->getMessage()) {
            $keywords = PlaceAdController::parseKeywords($message->getText());

            if (empty($keywords)) {
                return $this->actionEditKeywords($adsPostSearchId);
            }

            $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

            $adsPostSearch->unlinkAll('keywords', true);
            foreach ($keywords as $keyword) {
                $adKeyword = AdKeyword::find()->where([
                    'word' => $keyword,
                ])->one();

                if (!isset($adKeyword)) {
                    $adKeyword = new AdKeyword();

                    $adKeyword->setAttributes([
                        'word' => $keyword,
                    ]);
                    $adKeyword->save();
                }

                $adsPostSearch->link('keywords', $adKeyword);
            }

            $adsPostSearch->markToUpdateMatches();

            return $this->actionSearch($adsPostSearchId);
        }
    }

    public function actionEditLocation($adsPostSearchId)
    {
        $this->getState()->setName(self::createRoute('new-location-send', ['adsPostSearchId' => $adsPostSearchId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-location'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('new-location-my', ['adsPostSearchId' => $adsPostSearchId]),
                            'text' => Yii::t('bot', 'My location'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit', ['adsPostSearchId' => $adsPostSearchId]),
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

    public function actionNewLocationMy($adsPostSearchId)
    {
        $latitude = $this->getTelegramUser()->location_lat;
        $longitude = $this->getTelegramUser()->location_lon;

        return $this->actionNewLocationSet($adsPostSearchId, $latitude, $longitude);
    }

    public function actionNewLocationSend($adsPostSearchId)
    {
        $message = $this->getUpdate()->getMessage();

        if ($message && $message->getLocation()) {
            $latitude = $message->getLocation()->getLatitude();
            $longitude = $message->getLocation()->getLongitude();
        } elseif ($message && $message->getText() && AdsPost::validateLocation($message->getText())) {
            $latitude = AdsPost::getLatitudeFromText($message->getText());
            $longitude = AdsPost::getLongitudeFromText($message->getText());
        } else {
            $latitude = null;
            $longitude = null;
        }

        return $this->actionNewLocationSet($adsPostSearchId, $latitude, $longitude);
    }

    public function actionNewLocationSet($adsPostSearchId, $latitude, $longitude)
    {
        if ($latitude && $longitude) {
            $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

            $adsPostSearch->setAttributes([
                'location_latitude' => strval($latitude),
                'location_longitude' => strval($longitude),
            ]);
            $adsPostSearch->save();

            return $this->actionSearch($adsPostSearchId);
        } else {
            return $this->actionEditLocation($adsPostSearchId);
        }
    }

    public function actionEditRadius($adsPostSearchId)
    {
        $this->getState()->setName(self::createRoute('new-radius', ['adsPostSearchId' => $adsPostSearchId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-radius'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit', ['adsPostSearchId' => $adsPostSearchId]),
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

    public function actionNewRadius($adsPostSearchId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            if (!AdsPost::validateRadius($message->getText())) {
                return $this->actionEditRadius($adsPostSearchId);
            }

            $radius = $message->getText();

            $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

            $adsPostSearch->setAttributes([
                'radius' => $radius,
            ]);
            $adsPostSearch->save();
            $adsPostSearch->markToUpdateMatches();

            return $this->actionSearch($adsPostSearchId);
        }
    }

    public function actionStatus($adsPostSearchId)
    {
        $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

        $adsPostSearch->setAttributes([
            'status' => ($adsPostSearch->isActive() ? AdsPostSearch::STATUS_NOT_ACTIVATED : AdsPostSearch::STATUS_ACTIVATED),
        ]);
        $adsPostSearch->save();

        if ($adsPostSearch->isActive()) {
            $adsPostSearch->markToUpdateMatches();
        } else {
            $adsPostSearch->unlinkAll('matches', true);
            $adsPostSearch->setAttributes([
                'edited_at' => null,
            ]);
            $adsPostSearch->save();
        }

        return $this->actionSearch($adsPostSearchId);
    }

    public function actionPostMatches($adsPostSearchId, $page = 1)
    {
        $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

        $adsPostsQuery = $adsPostSearch->getMatches();

        if ($adsPostsQuery->count() == 0) {
            return $this->actionSearch($adsPostSearchId);
        }

        $pagination = new Pagination([
            'totalCount' => $adsPostsQuery->count(),
            'pageSize' => 1,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($adsPostSearchId) {
            return self::createRoute('post-matches', [
                'adsPostSearchId' => $adsPostSearchId,
                'page' => $page,
            ]);
        });

        $buttons = [];

        $buttons[] = $paginationButtons;
        $buttons[] = [
            [
                'callback_data' => self::createRoute('search', ['adsPostSearchId' => $adsPostSearchId]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        $adsPost = $adsPostsQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all()[0];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->sendPhotoOrEditMessageTextOrSendMessage(
                $adsPost->getPhotos()->count() ? $adsPost->getPhotos()->one()->file_id : null,
                $this->render('post-matches', [
                    'adsPost' => $adsPost,
                    'user' => TelegramUser::findOne($adsPost->user_id),
                    'currency' => Currency::findOne($adsPost->currency_id),
                    'categoryName' => AdCategory::getPlaceName($adsPost->category_id),
                    'keywords' => self::getKeywordsAsString($adsPost->getKeywords()->all()),
                    'locationLink' => ExternalLink::getOSMLink($adsPost->location_lat, $adsPost->location_lon),
                ]),
                $buttons,
                true
            )
            ->build();
    }

    public function actionConfirmDelete($adsPostSearchId)
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('confirm-delete'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('search', ['adsPostSearchId' => $adsPostSearchId]),
                            'text' => '❌',
                        ],
                        [
                            'callback_data' => self::createRoute('delete', ['adsPostSearchId' => $adsPostSearchId]),
                            'text' => '✅',
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionDelete($adsPostSearchId)
    {
        $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

        if (isset($adsPostSearch)) {
            $adCategoryId = $adsPostSearch->category_id;

            $adsPostSearch->unlinkAll('keywords', true);

            $adsPostSearch->delete();

            return $this->actionIndex($adCategoryId);
        }
    }

    public function actionShowAdsPost($adsPostId)
    {
        $adsPost = AdsPost::find($adsPostId)->one();

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('show-ads-post', [
                    'title' => $adsPost->title,
                    'description' => $adsPost->description,
                    'price' => $adsPost->price,
                    'latitude' => $adsPost->location_lat,
                    'longitude' => $adsPost->location_lon,
                    'user_id' => $this->getTelegramUser()->id,
                    'user_first_name' => $this->getTelegramUser()->provider_user_first_name,
                    'user_last_name' => $this->getTelegramUser()->provider_user_last_name,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('show'),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }
}
