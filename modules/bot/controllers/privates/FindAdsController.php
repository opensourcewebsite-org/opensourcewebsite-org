<?php
namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\models\AdCategory;
use app\modules\bot\models\UserSetting;
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
        $setting = $this->getTelegramUser()->getSetting(UserSetting::FIND_AD_CATEGORY_ID);

        if (!isset($setting)) {
            $setting = new UserSetting();

            $setting->setAttributes([
                'user_id' => $this->getTelegramUser()->id,
                'setting' => UserSetting::FIND_AD_CATEGORY_ID,
                'value' => $adCategoryId,
            ]);
        }

        $setting->value = $adCategoryId;
        $setting->save();

        $this->getState()->setName(self::createRoute('keywords'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('add'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'adCategoryId' => $this->getTelegramUser()->getSetting(UserSetting::FIND_AD_CATEGORY_ID)->value,
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
                return $this->actionAdd($this->getTelegramUser()->getSetting(UserSetting::FIND_AD_CATEGORY_ID)->value);
            }

            UserSetting::deleteAll([
                'and',
                ['user_id' => $this->getTelegramUser()->id,],
                ['like', 'setting', 'find_ad_keyword_'],
            ]);

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

                $setting = new UserSetting();

                $setting->setAttributes([
                    'user_id' => $this->getTelegramUser()->id,
                    'setting' => 'find_ad_keyword_' . $index,
                    'value' => strval($adKeyword->id),
                ]);

                $setting->save();
            }
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
                    'adCategoryId' => $this->getTelegramUser()->getSetting(UserSetting::FIND_AD_CATEGORY_ID)->value,
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
                $this->render('keywords'),
                $buttons
            )
            ->build();
    }

    public function actionCurrencySet($currencyId)
    {
        $currencySetting = $this->getTelegramUser()->getSetting(UserSetting::FIND_AD_CURRENCY_ID);

        if (!isset($currencySetting)) {
            $currencySetting = new UserSetting();

            $currencySetting->setAttributes([
                'user_id' => $this->getTelegramUser()->id,
                'setting' => UserSetting::FIND_AD_CURRENCY_ID,
            ]);
        }

        $currencySetting->setAttributes([
            'value' => strval($currencyId),
        ]);
        $currencySetting->save();

        return $this->actionCurrency();
    }

    public function actionCurrency()
    {
        $this->getState()->setName(self::createRoute('max-price-send'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('currency'),
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
        $currencySetting = $this->getTelegramUser()->getSetting(UserSetting::FIND_AD_CURRENCY_ID);

        if (!isset($currencySetting)) {
            $currencySetting = new UserSetting();

            $currencySetting->setAttributes([
                'user_id' => $this->getTelegramUser()->id,
                'setting' => UserSetting::FIND_AD_CURRENCY_ID,
            ]);
        }

        $currencySetting->setAttributes([
            'value' => null,
        ]);
        $currencySetting->save();

        return $this->actionMaxPriceSkip();
    }

    public function actionMaxPriceSend()
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            if (!UserSetting::validatePrice($message->getText())) {
                return $this->actionKeywords();
            }

            $maxPrice = intval(doubleval($message->getText()) * 100);

            $setting = $this->getTelegramUser()->getSetting(UserSetting::FIND_AD_MAX_PRICE);

            if (!isset($setting)) {
                $setting = new UserSetting();

                $setting->setAttributes([
                    'user_id' => $this->getTelegramUser()->id,
                    'setting' => UserSetting::FIND_AD_MAX_PRICE,
                ]);
            }

            $setting->setAttributes([
                'value' => strval($maxPrice),
            ]);

            $setting->save();

            return $this->actionMaxPrice();
        }
    }

    public function actionMaxPriceSkip()
    {
        $setting = $this->getTelegramUser()->getSetting(UserSetting::FIND_AD_MAX_PRICE);

        if (!isset($setting)) {
            $setting = new UserSetting();

            $setting->setAttributes([
                'user_id' => $this->getTelegramUser()->id,
                'setting' => UserSetting::FIND_AD_MAX_PRICE,
            ]);
        }

        $setting->setAttributes([
            'value' => null,
        ]);

        $setting->save();

        return $this->actionMaxPrice();
    }

    public function actionMaxPrice()
    {
        $buttons = [];

        if ($this->getTelegramUser()->location_lat !== null) {
            $buttons[][] = [
                'callback_data' => self::createRoute('location', ['userLocation' => true]),
                'text' => Yii::t('bot', 'My location'),
            ];
        }

        $buttons[] = [
            [
                'callback_data' => $this->getTelegramUser()->getSetting(UserSetting::FIND_AD_CURRENCY_ID)->value === null ? self::createRoute('keywords') : self::createRoute('currency'),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('max-price'),
                $buttons
            )
            ->build();
    }

    public function actionLocation($update = true, $userLocation = false)
    {
        $message = $this->getUpdate()->getMessage();
        $isLocationInText = $message && $message->getText() && UserSetting::validateLocation($message->getText());

        if ($update
            && (($message && $message->getLocation())
            || $userLocation
            || $isLocationInText
        )) {
            if ($userLocation) {
                $latitude = $this->getTelegramUser()->location_lat;
                $longitude = $this->getTelegramUser()->location_lon;
            } elseif ($message->getLocation()) {
                $latitude = $message->getLocation()->getLatitude();
                $longitude = $message->getLocation()->getLongitude();
            } else {
                $latitude = UserSetting::getLatitudeFromText($message->getText());
                $longitude = UserSetting::getLongitudeFromText($message->getText());
            }

            $setting = $this->getTelegramUser()->getSetting(UserSetting::FIND_AD_LOCATION_LATITUDE);

            if (!isset($setting)) {
                $setting = new UserSetting();

                $setting->setAttributes([
                    'user_id' => $this->getTelegramUser()->id,
                    'setting' => UserSetting::FIND_AD_LOCATION_LATITUDE,
                    'value' => strval($latitude),
                ]);
            }

            $setting->value = strval($latitude);
            $setting->save();

            $setting = $this->getTelegramUser()->getSetting(UserSetting::FIND_AD_LOCATION_LONGITUDE);

            if (!isset($setting)) {
                $setting = new UserSetting();

                $setting->setAttributes([
                    'user_id' => $this->getTelegramUser()->id,
                    'setting' => UserSetting::FIND_AD_LOCATION_LONGITUDE,
                    'value' => strval($longitude),
                ]);
            }

            $setting->value = strval($longitude);
            $setting->save();
        }

        if (!$update || ($update && (($message && $message->getLocation()) || $userLocation || $isLocationInText))) {
            $this->getState()->setName(self::createRoute('radius'));

            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->editMessageTextOrSendMessage(
                    $this->render('location'),
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
    }

    public function actionRadius()
    {
        $radius = $this->getUpdate()->getMessage()->getText();

        if (!UserSetting::validateRadius($radius)) {
            return $this->actionLocation(false);
        }

        $setting = $this->getTelegramUser()->getSetting(UserSetting::FIND_AD_RADIUS);

        if (!isset($setting)) {
            $setting = new UserSetting();

            $setting->setAttributes([
                'user_id' => $this->getTelegramUser()->id,
                'setting' => UserSetting::FIND_AD_RADIUS,
                'value' => $radius,
            ]);
        }

        $setting->value = $radius;
        $setting->save();

        return $this->actionMakeSearch();
    }

    private function getFindKeywords()
    {
        $adKeywords = [];

        foreach (UserSetting::find()->where([
            'and',
            ['user_id' => $this->getTelegramUser()->id],
            ['like', 'setting', 'find_ad_keyword_'],
        ])->all() as $adKeywordSetting) {
            $adKeywords[] = AdKeyword::findOne($adKeywordSetting->value);
        }

        return $adKeywords;
    }

    public function actionMakeSearch()
    {
        $adsPostSearch = new AdsPostSearch();

        $user = $this->getTelegramUser();

        $adsPostSearch->setAttributes([
            'user_id' => $user->id,
            'category_id' => intval($user->getSetting(UserSetting::FIND_AD_CATEGORY_ID)->value),
            'radius' => $user->getSetting(UserSetting::FIND_AD_RADIUS)->value,
            'currency_id' => $user->getSetting(UserSetting::FIND_AD_CURRENCY_ID)->value,
            'max_price' => $user->getSetting(UserSetting::FIND_AD_MAX_PRICE)->value,
            'location_latitude' => $user->getSetting(UserSetting::FIND_AD_LOCATION_LATITUDE)->value,
            'location_longitude' => $user->getSetting(UserSetting::FIND_AD_LOCATION_LONGITUDE)->value,
            'updated_at' => time(),
            'status' => AdsPostSearch::STATUS_NOT_ACTIVATED,
            'edited_at' => time(),
        ]);

        $adsPostSearch->save();

        $adKeywords = $this->getFindKeywords();

        foreach ($adKeywords as $adKeyword) {
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

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit-keywords', ['adsPostSearchId' => $adsPostSearchId]),
                            'text' => Yii::t('bot', 'Keywords'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-currency', ['adsPostSearchId' => $adsPostSearchId]),
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
                ]
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

        if (isset($adsPostSearch->currency_id) && $userCurrencyId != $adsPostSearch->currency_id) {
            $buttons[][] = [
                'callback_data' => self::createRoute('edit-currency-set', [
                    'adsPostSearchId' => $adsPostSearchId,
                    'currencyId' => $adsPostSearch->currency_id,
                ]),
                'text' => '· ' . Currency::findOne($adsPostSearch->currency_id)->code . ' - ' . Currency::findOne($adsPostSearch->currency_id)->name . ' ·',
            ];
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
                'callback_data' => self::createRoute('edit', ['adsPostSearchId' => $adsPostSearchId]),
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
        $this->getState()->setName(self::createRoute('edit-max-price-set', ['adsPostSearchId' => $adsPostSearchId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-max-price'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit-currency', ['adsPostSearchId' => $adsPostSearchId]),
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
            if (!UserSetting::validatePrice($message->getText())) {
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
        $this->getState()->setName(self::createRoute('new-location', ['adsPostSearchId' => $adsPostSearchId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-location'),
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

    public function actionNewLocation($adsPostSearchId)
    {
        $message = $this->getUpdate()->getMessage();
        $isLocationInText = $message->getText() && UserSetting::validateLocation($message->getText());

        if ($message && ($message->getLocation() || $isLocationInText)) {
            if ($message->getLocation()) {
                $latitude = strval($message->getLocation()->getLatitude());
                $longitude = strval($message->getLocation()->getLongitude());
            } else {
                $latitude = UserSetting::getLatitudeFromText($message->getText());
                $longitude = UserSetting::getLongitudeFromText($message->getText());
            }

            $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

            $adsPostSearch->setAttributes([
                'location_latitude' => $latitude,
                'location_longitude' => $longitude,
            ]);
            $adsPostSearch->save();
            $adsPostSearch->markToUpdateMatches();

            return $this->actionSearch($adsPostSearchId);
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
        if ($message = $this->getUpdate()->getMessage()) {
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

        return $this->actionSearch($adsPostSearchId);
    }

    public function actionPostMatches($adsPostSearchId, $page = 1)
    {
        $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

        $adsPosts = $adsPostSearch->getMatches()->all();

        if (empty($adsPosts)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery($this->render('no-post-matches'))
                ->build();
        }

        $pagination = new Pagination([
            'totalCount' => count($adsPosts),
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

        $adsPost = $adsPosts[$page - 1];

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
