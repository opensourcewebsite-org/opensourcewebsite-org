<?php
namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\AdCategory;
use app\modules\bot\models\UserSetting;
use app\modules\bot\models\AdKeyword;
use app\modules\bot\models\AdsPost;
use app\modules\bot\models\AdsPostSearch;
use yii\data\Pagination;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\User;
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
                    'categoryName' => AdCategory::findOne($adCategoryId)->find_name,
                ]),
                $buttons
            )
            ->build();
    }

    private static function getKeywordsAsString($adKeywords) {
        $reply = '';

        foreach ($adKeywords as $adKeyword) {
            if (!empty($reply)) {
                $reply .= ' ';
            }

            $reply .= $adKeyword->word;
        }

        return $reply;
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

    public function actionKeywords()
    {
        if ($message = $this->getUpdate()->getMessage()) {
            $keywords = PlaceAdController::parseKeywords($message->getText());

            if (empty($keywords)) {
                return ResponseBuilder::fromUpdate($this->getUpdate())
                    ->editMessageTextOrSendMessage($this->render('keywords-error'))
                    ->merge($this->actionAdd($this->getTelegramUser()->getSetting(UserSetting::FIND_AD_CATEGORY_ID)->value))
                    ->build();
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

        $this->getState()->setName(self::createRoute('location'));

        $buttons = [];

        if ($this->getTelegramUser()->location_lat !== null) {
            $buttons[][] = [
                'callback_data' => self::createRoute('location', ['userLocation' => true]),
                'text' => Yii::t('bot', 'Использовать мою геолокацию'),
            ];
        }

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

    public function actionLocation($update = true, $userLocation = false)
    {
        if ($update && ((($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getLocation()) || $userLocation)) {

            if ($userLocation) {
                $latitude = $this->getTelegramUser()->location_lat;
                $longitude = $this->getTelegramUser()->location_lon;
            } else {
                $latitude = $message->getLocation()->getLatitude();
                $longitude = $message->getLocation()->getLongitude();
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

        if (!$update || ($update && (($this->getUpdate()->getMessage() && $this->getUpdate()->getMessage()->getLocation()) || $userLocation))) {

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
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->editMessageTextOrSendMessage($this->render('radius-error'))
                ->merge($this->actionLocation(false))
                ->build();
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

        return $this->actionCheck();
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

    public function actionCheck()
    {
        $user = $this->getTelegramUser();

        $this->getState()->setName(null);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('check', [
                    'keywords' => self::getKeywordsAsString($this->getFindKeywords()),
                    'latitude' => $user->getSetting(UserSetting::FIND_AD_LOCATION_LATITUDE)->value,
                    'longitude' => $user->getSetting(UserSetting::FIND_AD_LOCATION_LONGITUDE)->value,
                    'radius' => $user->getSetting(UserSetting::FIND_AD_RADIUS)->value,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('find-edit'),
                            'text' => Emoji::EDIT,
                        ],
                        [
                            'callback_data' => self::createRoute('make-search'),
                            'text' => '✅',
                        ],
                    ],
                    [
                        [
                            'callback_data' => AdsController::createRoute(),
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

    public function actionFindEdit()
    {
        $this->getState()->setName(null);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('find-edit-keywords'),
                            'text' => Yii::t('bot', 'Ключевые слова'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('find-edit-location'),
                            'text' => Yii::t('bot', 'География'),
                        ],
                        [
                            'callback_data' => self::createRoute('find-edit-radius'),
                            'text' => Yii::t('bot', 'Радиус поиска'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('check'),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ]
                    ],
                ]
            )
            ->build();
    }

    public function actionFindEditKeywords()
    {
        $this->getState()->setName(self::createRoute('find-new-keywords'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-keywords'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('find-edit'),
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

    public function actionFindNewKeywords()
    {
        if ($message = $this->getUpdate()->getMessage()) {
            $keywords = PlaceAdController::parseKeywords($message->getText());

            if (empty($keywords)) {
                return ResponseBuilder::fromUpdate($this->getUpdate())
                    ->editMessageTextOrSendMessage($this->render('keywords-error'))
                    ->merge($this->actionFindEditKeywords())
                    ->build();
            }

            UserSetting::deleteAll([
                'and',
                ['user_id' => $this->getTelegramUser()->id],
                ['like', 'setting', 'find_ad_keyword_'],
            ]);

            foreach ($keywords as $index => $keyword) {
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

                $setting = new UserSetting();
                $setting->setAttributes([
                    'user_id' => $this->getTelegramUser()->id,
                    'setting' => 'find_ad_keyword_' . $index,
                    'value' => strval($adKeyword->id),
                ]);
                $setting->save();
            }

            return $this->actionCheck();
        }
    }

    public function actionFindEditLocation()
    {
        $this->getState()->setName(self::createRoute('find-new-location'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-location'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('find-edit'),
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

    public function actionFindNewLocation()
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getLocation()) {
            $latitude = strval($message->getLocation()->getLatitude());
            $longitude = strval($message->getLocation()->getLongitude());

            $latitudeSetting = $this->getTelegramUser()->getSetting(UserSetting::FIND_AD_LOCATION_LATITUDE);

            $latitudeSetting->setAttributes([
                'value' => $latitude,
            ]);
            $latitudeSetting->save();

            $longitudeSetting = $this->getTelegramUser()->getSetting(UserSetting::FIND_AD_LOCATION_LONGITUDE);

            $longitudeSetting->setAttributes([
                'value' => $longitude,
            ]);
            $longitudeSetting->save();

            return $this->actionCheck();
        }
    }

    public function actionFindEditRadius()
    {
        $this->getState()->setName(self::createRoute('find-new-radius'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-radius'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('find-edit'),
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

    public function actionFindNewRadius()
    {
        if ($message = $this->getUpdate()->getMessage()) {
            $radius = $message->getText();

            if (!UserSetting::validateRadius($radius)) {
                return ResponseBuilder::fromUpdate($this->getUpdate())
                    ->editMessageTextOrSendMessage($this->render('radius-error'))
                    ->merge($this->actionFindEditRadius())
                    ->build();
            }

            $radiusSetting = $this->getTelegramUser()->getSetting(UserSetting::FIND_AD_RADIUS);

            $radiusSetting->setAttributes([
                'value' => $radius,
            ]);
            $radiusSetting->save();

            return $this->actionCheck();
        }
    }

    public function actionMakeSearch()
    {
        $adsPostSearch = new AdsPostSearch();

        $user = $this->getTelegramUser();

        $adsPostSearch->setAttributes([
            'user_id' => $user->id,
            'category_id' => intval($user->getSetting(UserSetting::FIND_AD_CATEGORY_ID)->value),
            'radius' => $user->getSetting(UserSetting::FIND_AD_RADIUS)->value,
            'location_latitude' => $user->getSetting(UserSetting::FIND_AD_LOCATION_LATITUDE)->value,
            'location_longitude' => $user->getSetting(UserSetting::FIND_AD_LOCATION_LONGITUDE)->value,
            'updated_at' => time(),
            'status' => AdsPostSearch::STATUS_ACTIVATED,
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
        $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('search', [
                    'categoryName' => AdCategory::findOne($adsPostSearch->category_id)->find_name,
                    'keywords' => self::getKeywordsAsString($adsPostSearch->getKeywords()->all()),
                    'adsPostSearch' => $adsPostSearch,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('status', ['adsPostSearchId' => $adsPostSearchId]),
                            'text' => 'Status: ' . ($adsPostSearch->isActive() ? 'ON' : 'OFF'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('post-matches', ['adsPostSearchId' => $adsPostSearchId]),
                            'text' => '🙋‍♂️ ' . count($this->getMatchedPosts($adsPostSearch)),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('index', ['adCategoryId' => $adsPostSearch->category_id]),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => self::createRoute('update', ['adsPostSearchId' => $adsPostSearchId]),
                            'text' => '🔄',
                        ],
                        [
                            'callback_data' => self::createRoute('edit', ['adsPostSearchId' => $adsPostSearchId]),
                            'text' => Emoji::EDIT,
                        ],
                        [
                            'callback_data' => self::createRoute('confirm-delete', ['adsPostSearchId' => $adsPostSearchId]),
                            'text' => Emoji::DELETE,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionUpdate($adsPostSearchId)
    {
        $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

        $adsPostSearch->setAttributes([
            'updated_at' => time(),
        ]);
        $adsPostSearch->save();

        return $this->actionSearch($adsPostSearchId);
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
                            'text' => Yii::t('bot', 'Ключевые слова'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-location', ['adsPostSearchId' => $adsPostSearchId]),
                            'text' => Yii::t('bot', 'География'),
                        ],
                        [
                            'callback_data' => self::createRoute('edit-radius', ['adsPostSearchId' => $adsPostSearchId]),
                            'text' => Yii::t('bot', 'Радиус'),
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
                return ResponseBuilder::fromUpdate($this->getUpdate())
                    ->editMessageTextOrSendMessage($this->render('keywords-error'))
                    ->merge($this->actionEditKeywords($adsPostSearchId))
                    ->build();
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
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getLocation()) {
            $latitude = strval($message->getLocation()->getLatitude());
            $longitude = strval($message->getLocation()->getLongitude());

            $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

            $adsPostSearch->setAttributes([
                'location_latitude' => $latitude,
                'location_longitude' => $longitude,
            ]);
            $adsPostSearch->save();

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

    private function getMatchedPosts($adsPostSearch)
    {
        $allAdsPosts = AdsPost::find()
            ->where([
                'category_id' => $adsPostSearch->category_id,
                'status' => AdsPost::STATUS_ACTIVATED,
            ])
            ->andWhere(['>=', 'updated_at', time() - 14 * 24 * 60 * 60])
            ->all();

        $adsPosts = [];

        foreach ($allAdsPosts as $adsPostToCheck) {
            if ($adsPostSearch->matches($adsPostToCheck)) {
                $adsPosts[] = $adsPostToCheck;
            }
        }

        return $adsPosts;
    }

    public function actionPostMatches($adsPostSearchId, $page = 1)
    {
        $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

        $adsPosts = $this->getMatchedPosts($adsPostSearch);

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
                $adsPost->photo_file_id,
                $this->render('post-matches', [
                    'adsPost' => $adsPost,
                    'user' => User::findOne($adsPost->user_id),
                    'currency' => Currency::findOne($adsPost->currency_id),
                    'categoryName' => AdCategory::findOne($adsPost->category_id)->place_name,
                    'keywords' => self::getKeywordsAsString($adsPost->getKeywords()->all()),
                ]),
                $buttons
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

    public function actionShow()
    {
        $buttons = [];

        foreach (AdsPost::find()->all() as $adsPost) {
            $buttons[][] = [
                'callback_data' => self::createRoute('show-ads-post', ['adsPostId' => $adsPost->id]),
                'text' => $adsPost->price . ' | ' . $adsPost->title,
            ];
        }

        $buttons[][] = [
            'callback_data' => self::createRoute('save_search'),
            'text' => Yii::t('bot', 'Сохранить поиск'),
        ];

        $buttons[][] = [
            'callback_data' => AdsController::createRoute(),
            'text' => Emoji::BACK,
        ];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('show'),
                $buttons
            )
            ->build();
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

    public function actionSaveSearch()
    {
        $adsPostSearch = new AdsPostSearch();

        $adsPostSearch->setAttributes([
            'user_id' => $this->getTelegramUser()->id,
            'radius' => $this->getTelegramUser()->getSetting(UserSetting::FIND_AD_RADIUS)->value,
            'location_latitude' => $this->getTelegramUser()->getSetting(UserSetting::FIND_AD_LOCATION_LATITUDE)->value,
            'location_longitude' => $this->getTelegramUser()->getSetting(UserSetting::FIND_AD_LOCATION_LONGITUDE)->value,
            'updated_at' => time(),
            'status' => AdsPostSearch::STATUS_ACTIVATED,
        ]);

        $adsPostSearch->save();

        foreach (UserSetting::find()->where([
            'and',
            ['user_id' => $this->getTelegramUser()->id],
            ['like', 'setting', 'find_ad_keyword_'],
        ])->all() as $keywordSetting) {
            $adKeyword = AdKeyword::find($keywordSetting->value)->one();

            $adsPostSearch->link('keywords', $adKeyword);
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('save-search'),
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
