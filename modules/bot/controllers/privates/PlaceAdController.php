<?php
namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\models\UserSetting;
use app\modules\bot\models\AdKeyword;
use app\modules\bot\models\AdsPost;
use app\modules\bot\models\AdCategory;
use app\modules\bot\models\AdsPostSearch;
use app\modules\bot\models\User as TelegramUser;
use app\modules\bot\models\AdPhoto;
use app\models\User;
use yii\data\Pagination;
use app\modules\bot\components\helpers\PaginationButtons;
use app\models\Currency;

class PlaceAdController extends Controller
{
    public function actionIndex($adCategoryId, $page = 1)
    {
        $this->getState()->setName(null);

        $buttons = [];

        $adsPostQuery = AdsPost::find()->where([
            'user_id' => $this->getTelegramUser()->id,
            'category_id' => $adCategoryId,
        ]);

        $adsPostCount = $adsPostQuery->count();

        $pagination = new Pagination([
            'totalCount' => $adsPostCount,
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        foreach ($adsPostQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all() as $adsPost) {
            $buttons[][] = [
                'text' => ($adsPost->isActive() ? '' : '❌ ') . $adsPost->title,
                'callback_data' => self::createRoute('post', ['adsPostId' => $adsPost->id]),
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
                $this->render('index', ['categoryName' => AdCategory::getPlaceName($adCategoryId)]),
                $buttons
            )
            ->build();
    }

    public function actionAdd($adCategoryId)
    {
        $this->getState()->setName(self::createRoute('title'));

        $setting = $this->getTelegramUser()->getSetting(UserSetting::PLACE_AD_CATEGORY_ID);

        if (!isset($setting)) {
            $setting = new UserSetting();

            $setting->setAttributes([
                'user_id' => $this->getTelegramUser()->id,
                'setting' => UserSetting::PLACE_AD_CATEGORY_ID,
                'value' => strval($adCategoryId),
            ]);
        }

        $setting->value = strval($adCategoryId);
        $setting->save();

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('add'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('index', ['adCategoryId' => $adCategoryId]),
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

    public function actionEdit($adsPostId)
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit-title', ['adsPostId' => $adsPostId]),
                            'text' => Yii::t('bot', 'Title'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-description', ['adsPostId' => $adsPostId]),
                            'text' => Yii::t('bot', 'Description and photo'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-keywords', ['adsPostId' => $adsPostId]),
                            'text' => Yii::t('bot', 'Keywords'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-currency', ['adsPostId' => $adsPostId]),
                            'text' => Yii::t('bot', 'Currency'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-price', ['adsPostId' => $adsPostId]),
                            'text' => Yii::t('bot', 'Price'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-location', ['adsPostId' => $adsPostId]),
                            'text' => Yii::t('bot', 'Location'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-radius', ['adsPostId' => $adsPostId]),
                            'text' => Yii::t('bot', 'Delivery radius'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('post', ['adsPostId' => $adsPostId]),
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

    private function getMatchedPostSearches($adsPost)
    {
        $adsPostSearches = AdsPostSearch::find()
            ->where([
                'category_id' => $adsPost->category_id,
                'status' => AdsPostSearch::STATUS_ACTIVATED,
            ])
            ->andWhere(['>=', 'updated_at', time() - 14 * 24 * 60 * 60])
            ->all();

        $matchedPostSearches = [];

        foreach ($adsPostSearches as $adsPostSearch) {
            if ($adsPostSearch->matches($adsPost)) {
                $matchedPostSearches[] = $adsPostSearch;
            }
        }

        return $matchedPostSearches;
    }

    public function actionPost($adsPostId)
    {
        $this->updatePost($adsPostId);

        $adsPost = AdsPost::findOne($adsPostId);

        $this->getState()->setName(null);

        $buttons = [];

        $buttons[][] = [
            'callback_data' => self::createRoute('status', ['adsPostId' => $adsPostId]),
            'text' => 'Status: ' . ($adsPost->isActive() ? 'ON' : 'OFF'),
        ];

        $matchedPostSearchesCount = count($this->getMatchedPostSearches($adsPost));

        if ($matchedPostSearchesCount > 0) {
            $buttons[][] = [
                'callback_data' => self::createRoute('matched-post-searches', ['adsPostId' => $adsPostId]),
                'text' => '🙋‍♂️ ' . $matchedPostSearchesCount,
            ];
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('index', ['adCategoryId' => $adsPost->category_id]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'callback_data' => self::createRoute('edit', ['adsPostId' => $adsPostId]),
                'text' => Emoji::EDIT,
            ],
            [
                'callback_data' => self::createRoute('confirm-delete', ['adsPostId' => $adsPostId]),
                'text' => Emoji::DELETE,
            ],
        ];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->sendPhotoOrEditMessageTextOrSendMessage(
                $adsPost->getPhotos()->count() ? $adsPost->getPhotos()->one()->file_id : null,
                $this->render('post', [
                    'adsPost' => $adsPost,
                    'currency' => Currency::findOne($adsPost->currency_id),
                    'categoryName' => AdCategory::getPlaceName($adsPost->category_id),
                    'keywords' => self::getKeywordsAsString($adsPost->getKeywords()->all()),
                    'locationLink' => ExternalLink::getOSMLink($adsPost->location_lat, $adsPost->location_lon),
                    'liveDays' => AdsPost::LIVE_DAYS,
                ]),
                $buttons,
                true
            )
            ->build();
    }

    public function updatePost($adsPostId)
    {
        $adsPost = AdsPost::findOne($adsPostId);

        $adsPost->setAttributes([
            'updated_at' => time(),
        ]);
        $adsPost->save();
    }

    public function actionMatchedPostSearches($adsPostId, $page = 1)
    {
        $adsPost = AdsPost::findOne($adsPostId);

        $matchedPostSearches = $this->getMatchedPostSearches($adsPost);

        if (empty($matchedPostSearches)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery($this->render('no-matched-post-searches'))
                ->build();
        }

        $pagination = new Pagination([
            'totalCount' => count($matchedPostSearches),
            'pageSize' => 1,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $buttons = [];

        $buttons[] = PaginationButtons::build($pagination, function ($page) use ($adsPostId) {
            return self::createRoute('matched-post-searches', [
                'adsPostId' => $adsPostId,
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'callback_data' => self::createRoute('post', ['adsPostId' => $adsPostId]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        $matchedPostSearch = $matchedPostSearches[$page - 1];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('matched-post-searches', [
                    'categoryName' => AdCategory::getFindName($matchedPostSearch->category_id),
                    'adsPostSearch' => $matchedPostSearch,
                    'user' => TelegramUser::findOne($matchedPostSearch->user_id),
                    'keywords' => self::getKeywordsAsString($matchedPostSearch->getKeywords()->all()),
                    'locationLink' => ExternalLink::getOSMLink($matchedPostSearch->location_latitude, $matchedPostSearch->location_longitude),
                ]),
                $buttons,
                true
            )
            ->build();
    }

    public function actionEditTitle($adsPostId)
    {
        $this->getState()->setName(self::createRoute('new-title', ['adsPostId' => $adsPostId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-title'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit', ['adsPostId' => $adsPostId]),
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

    public function actionNewTitle($adsPostId)
    {
        if ($this->getUpdate()->getMessage()) {
            $adsPost = AdsPost::findOne($adsPostId);

            $title = $this->getUpdate()->getMessage()->getText();

            $adsPost->setAttributes([
                'title' => $title,
            ]);
            $adsPost->save();

            return $this->actionPost($adsPostId);
        }
    }

    public function actionEditDescription($adsPostId)
    {
        $this->getState()->setName(self::createRoute('new-description', ['adsPostId' => $adsPostId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-description'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('post', ['adsPostId' => $adsPostId]),
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

    public function actionNewDescription($adsPostId)
    {
        if ($message = $this->getUpdate()->getMessage()) {
            if ($message->getPhoto()) {
                $description = $message->getCaption() !== null ? $message->getCaption() : UserSetting::NO_DESCRIPTION;
                $photoFileId = $message->getPhoto()[0]->getFileId();
            } else {
                $description = $message->getText();
                $photoFileId = null;
            }

            $adsPost = AdsPost::findOne($adsPostId);

            $adsPost->description = strval($description);
            $adsPost->unlinkAll('photos', true);

            if ($photoFileId !== null) {
                $adPhoto = new AdPhoto();

                $adPhoto->setAttributes([
                    'ads_post_id' => $adsPost->id,
                    'file_id' => $photoFileId,
                ]);
                $adPhoto->save();

                $adsPost->link('photos', $adPhoto);
            }

            $adsPost->save();

            return $this->actionPost($adsPostId);
        }
    }

    public function actionEditKeywords($adsPostId)
    {
        $this->getState()->setName(self::createRoute('new-keywords', ['adsPostId' => $adsPostId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-keywords'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('post', ['adsPostId' => $adsPostId]),
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

    public function actionNewKeywords($adsPostId)
    {
        if ($this->getUpdate()->getMessage()) {
            $adsPost = AdsPost::findOne($adsPostId);

            $keywords = self::parseKeywords($this->getUpdate()->getMessage()->getText());

            if (empty($keywords)) {
                return ResponseBuilder::fromUpdate($this->getUpdate())
                    ->editMessageTextOrSendMessage($this->render('keywords-error'))
                    ->merge($this->actionEditKeywords($adsPostId))
                    ->build();
            }

            $adsPost->unlinkAll('keywords', true);

            foreach ($keywords as $keyword) {
                $adKeyword = AdKeyword::find()->where(['word' => $keyword])->one();

                if (!isset($adKeyword)) {
                    $adKeyword = new AdKeyword();

                    $adKeyword->setAttributes([
                        'word' => $keyword,
                    ]);

                    $adKeyword->save();
                }

                $adsPost->link('keywords', $adKeyword);
            }

            return $this->actionPost($adsPostId);
        }
    }

    public function actionEditCurrency($adsPostId, $page = 1)
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

        $telegramUser = $this->getTelegramUser();
        if ($telegramUser->user_id && User::findOne($telegramUser->user_id)) {
            $user = User::findOne($telegramUser->user_id);

            if ($user->currency_id !== null) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('new-currency', [
                        'adsPostId' => $adsPostId,
                        'currencyId' => $user->currency_id,
                    ]),
                    'text' => '· ' . Currency::findOne($user->currency_id)->code . ' - ' . Currency::findOne($user->currency_id)->name . ' ·',
                ];
            }
        }

        foreach ($currencyQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all() as $currency) {
            $buttons[][] = [
                'callback_data' => self::createRoute('new-currency', [
                    'adsPostId' => $adsPostId,
                    'currencyId' => $currency->id,
                ]),
                'text' => $currency->code . ' - ' . $currency->name,
            ];
        }

        $buttons[] = PaginationButtons::build($pagination, function ($page) use ($adsPostId) {
            return self::createRoute('edit-currency', [
                'adsPostId' => $adsPostId,
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'callback_data' => self::createRoute('edit', ['adsPostId' => $adsPostId]),
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

    public function actionNewCurrency($adsPostId, $currencyId)
    {
        $adsPost = AdsPost::findOne($adsPostId);

        if (isset($adsPost)) {
            $adsPost->setAttributes([
                'currency_id' => $currencyId,
            ]);
            $adsPost->save();
        }

        return $this->actionPost($adsPostId);
    }

    public function actionEditPrice($adsPostId)
    {
        $this->getState()->setName(self::createRoute('new-price', ['adsPostId' => $adsPostId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-price'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('post', ['adsPostId' => $adsPostId]),
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

    public function actionNewPrice($adsPostId)
    {
        if ($message = $this->getUpdate()->getMessage()) {
            if (!UserSetting::validatePrice($message->getText())) {
                return ResponseBuilder::fromUpdate($this->getUpdate())
                    ->editMessageTextOrSendMessage($this->render('price-error'))
                    ->merge($this->actionEditPrice($adsPostId))
                    ->build();
            }

            $price = $message->getText();

            $adsPost = AdsPost::findOne($adsPostId);

            $adsPost->setAttributes([
                'price' => intval(100.0 * doubleval($price)),
            ]);

            $adsPost->save();

            return $this->actionPost($adsPostId);
        }
    }

    public function actionEditLocation($adsPostId)
    {
        $this->getState()->setName(self::createRoute('new-location', ['adsPostId' => $adsPostId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-location'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('post', ['adsPostId' => $adsPostId]),
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

    public function actionNewLocation($adsPostId)
    {
        $isLocationInText = $this->getUpdate()->getMessage()->getText() && UserSetting::validateLocation($this->getUpdate()->getMessage()->getText());

        if (($message = $this->getUpdate()->getMessage())
            && ($location = $this->getUpdate()->getMessage()->getLocation())
            || $isLocationInText
        ) {
            if ($message->getLocation()) {
                $latitude = strval($location->getLatitude());
                $longitude = strval($location->getLongitude());
            } else {
                $latitude = UserSetting::getLatitudeFromText($message->getText());
                $longitude = UserSetting::getLongitudeFromText($message->getText());
            }

            $adsPost = AdsPost::findOne($adsPostId);

            $adsPost->setAttributes([
                'location_lat' => $latitude,
                'location_lon' => $longitude,
            ]);

            $adsPost->save();

            return $this->actionPost($adsPostId);
        }
    }

    public function actionEditRadius($adsPostId)
    {
        $this->getState()->setName(self::createRoute('new-radius', ['adsPostId' => $adsPostId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-radius'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('post', ['adsPostId' => $adsPostId]),
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

    public function actionNewRadius($adsPostId)
    {
        if ($message = $this->getUpdate()->getMessage()) {
            $adsPost = AdsPost::findOne($adsPostId);

            $deliveryKm = $message->getText();

            $adsPost->setAttributes([
                'delivery_km' => $deliveryKm,
            ]);

            $adsPost->save();

            return $this->actionPost($adsPostId);
        }
    }

    public function actionStatus($adsPostId)
    {
        $adsPost = AdsPost::findOne($adsPostId);

        if ($adsPost->isActive()) {
            $adsPost->status = AdsPost::STATUS_NOT_ACTIVATED;
        } else {
            $adsPost->status = AdsPost::STATUS_ACTIVATED;
        }

        $adsPost->save();

        return $this->actionPost($adsPostId);
    }

    private static function getKeywordsAsString($adKeywords)
    {
        $keywords = [];

        foreach ($adKeywords as $adKeyword) {
            $keywords[] = $adKeyword->word;
        }

        return implode(', ', $keywords);
    }

    public function actionTitle($update = true)
    {
        if ($update && $this->getUpdate()->getMessage() !== null) {
            $setting = $this->getTelegramUser()->getSetting(UserSetting::PLACE_AD_TITLE);

            if (!isset($setting)) {
                $setting = new UserSetting();

                $setting->setAttributes([
                    'user_id' => $this->getTelegramUser()->id,
                    'setting' => UserSetting::PLACE_AD_TITLE,
                    'value' => $this->getUpdate()->getMessage()->getText(),
                ]);
            }

            $setting->value = $this->getUpdate()->getMessage()->getText();
            $setting->save();
        }

        $this->getState()->setName(self::createRoute('keywords'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('title'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('add', [
                                'adCategoryId' => $this->getTelegramUser()->getSetting(UserSetting::PLACE_AD_CATEGORY_ID)->value,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public static function parseKeywords($text)
    {
        if (preg_match_all('/(^|[\.,\n])([^\.,\n]+)/', $text, $matches)) {
            return array_map('mb_strtolower', array_map('trim', $matches[2]));
        } else {
            return [];
        }
    }

    public function actionKeywords()
    {
        if ($this->getUpdate()->getMessage() !== null) {
            $keywords = self::parseKeywords($this->getUpdate()->getMessage()->getText());

            if (empty($keywords)) {
                return ResponseBuilder::fromUpdate($this->getUpdate())
                    ->editMessageTextOrSendMessage($this->render('keywords-error'))
                    ->merge($this->actionTitle(false))
                    ->build();
            }

            UserSetting::deleteAll([
                'and',
                ['user_id' => $this->getTelegramUser()->id],
                ['like', 'setting', 'place_ad_keyword_'],
            ]);

            foreach ($keywords as $index => $word) {
                $adKeyword = AdKeyword::find()->where(['word' => $word])->one();

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
                    'setting' => 'place_ad_keyword_' . $index,
                    'value' => strval($adKeyword->id),
                ]);

                $setting->save();
            }
        }

        $this->getState()->setName(self::createRoute('description'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('keywords'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('title'),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionConfirmDelete($adsPostId)
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('confirm-delete'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('post', ['adsPostId' => $adsPostId]),
                            'text' => '❌',
                        ],
                        [
                            'callback_data' => self::createRoute('delete', ['adsPostId' => $adsPostId]),
                            'text' => '✅',
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionDelete($adsPostId)
    {
        $adCategoryId = AdsPost::findOne($adsPostId)->category_id;

        AdsPost::findOne($adsPostId)->unlinkAll('keywords', true);
        AdsPost::findOne($adsPostId)->delete();

        return $this->actionIndex($adCategoryId);
    }

    public function actionDescription($page = 1)
    {
        if ($message = $this->getUpdate()->getMessage()) {

            if ($message->getPhoto() !== null) {
                $photoFileId = $message->getPhoto()[0]->getFileId();
                $description = $message->getCaption() !== null ? $message->getCaption() : UserSetting::NO_DESCRIPTION;
            } else {
                $photoFileId = UserSetting::NO_PHOTO_FILE_ID;
                $description = $message->getText();
            }

            $setting = $this->getTelegramUser()->getSetting(UserSetting::PLACE_AD_PHOTO_FILE_ID);

            if (!isset($setting)) {
                $setting = new UserSetting();

                $setting->setAttributes([
                    'user_id' => $this->getTelegramUser()->id,
                    'setting' => UserSetting::PLACE_AD_PHOTO_FILE_ID,
                ]);
            }

            $setting->value = $photoFileId;
            $setting->save();

            $setting = $this->getTelegramUser()->getSetting(UserSetting::PLACE_AD_DESCRIPTION);

            if (!isset($setting)) {
                $setting = new UserSetting();

                $setting->setAttributes([
                    'user_id' => $this->getTelegramUser()->id,
                    'setting' => UserSetting::PLACE_AD_DESCRIPTION,
                    'value' => $description,
                ]);
            }

            $setting->value = $description;
            $setting->save();
        }

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
                    'callback_data' => self::createRoute('currency', ['currencyId' => $user->currency_id]),
                    'text' => '· ' . Currency::findOne($user->currency_id)->code . ' - ' . Currency::findOne($user->currency_id)->name . ' ·',
                ];
            }
        }

        foreach ($currencyQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all() as $currency) {
            $buttons[][] = [
                'callback_data' => self::createRoute('currency', ['currencyId' => $currency->id]),
                'text' => $currency->code . ' - ' . $currency->name,
            ];
        }

        $buttons[] = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('description', ['page' => $page]);
        });

        $buttons[] = [
            [
                'callback_data' => self::createRoute('keywords'),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        $this->getState()->setName(null);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('description'),
                $buttons
            )
            ->build();
    }

    public function actionCurrency($currencyId = null)
    {
        if ($currencyId) {
            $setting = $this->getTelegramUser()->getSetting(UserSetting::PLACE_AD_CURRENCY_ID);

            if (!isset($setting)) {
                $setting = new UserSetting();
                $setting->setAttributes([
                    'user_id' => $this->getTelegramUser()->id,
                    'setting' => UserSetting::PLACE_AD_CURRENCY_ID,
                ]);
            }

            $setting->setAttributes([
                'value' => strval($currencyId),
            ]);
            $setting->save();
        }

        $this->getState()->setName(self::createRoute('price'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('currency', [
                    'currencyCode' => Currency::findOne(
                        $this->getTelegramUser()->getSetting(UserSetting::PLACE_AD_CURRENCY_ID)->value
                    )
                    ->code,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('description'),
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

    public function actionPrice($update = true)
    {
        if ($update && ($message = $this->getUpdate()->getMessage())) {
            if (!UserSetting::validatePrice($message->getText())) {
                return ResponseBuilder::fromUpdate($this->getUpdate())
                    ->editMessageTextOrSendMessage($this->render('price-error'))
                    ->merge($this->actionCurrency())
                    ->build();
            }

            $setting = $this->getTelegramUser()->getSetting(UserSetting::PLACE_AD_PRICE);

            if (!isset($setting)) {
                $setting = new UserSetting();

                $setting->setAttributes([
                    'user_id' => $this->getTelegramUser()->id,
                    'setting' => UserSetting::PLACE_AD_PRICE,
                ]);
            }

            $setting->value = strval(100.0 * doubleval($message->getText()));
            $setting->save();
        }

        $this->getState()->setName(self::createRoute('location'));

        $buttons = [];

        if ($this->getTelegramUser()->location_lat !== null) {
            $buttons[][] = [
                'callback_data' => self::createRoute('location', ['userLocation' => 1]),
                'text' => Yii::t('bot', 'My location'),
            ];
        }

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
                $this->render('price'),
                $buttons
            )
            ->build();
    }

    public function actionLocation($update = true, $userLocation = false)
    {
        $this->getState()->setName(self::createRoute('radius'));

        $message = $this->getUpdate()->getMessage();
        $isLocationInText = $message->getText() && UserSetting::validateLocation($message->getText());

        if ($update && (($message && $message->getLocation()) || $userLocation || $isLocationInText)) {
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

            $setting = $this->getTelegramUser()->getSetting(UserSetting::PLACE_AD_LOCATION_LAT);

            if (!isset($setting)) {
                $setting = new UserSetting();

                $setting->setAttributes([
                    'user_id' => $this->getTelegramUser()->id,
                    'setting' => UserSetting::PLACE_AD_LOCATION_LAT,
                    'value' => strval($latitude),
                ]);
            }

            $setting->value = strval($latitude);
            $setting->save();

            $setting = $this->getTelegramUser()->getSetting(UserSetting::PLACE_AD_LOCATION_LON);

            if (!isset($setting)) {
                $setting = new UserSetting();

                $setting->setAttributes([
                    'user_id' => $this->getTelegramUser()->id,
                    'setting' => UserSetting::PLACE_AD_LOCATION_LON,
                    'value' => strval($longitude),
                ]);
            }

            $setting->value = strval($longitude);
            $setting->save();
        } elseif ($update) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->editMessageTextOrSendMessage($this->render('location-error'))
                ->merge($this->actionPrice(false))
                ->build();
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('location'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('price'),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionRadius()
    {
        $radius = $this->getUpdate()->getMessage()->getText();

        if (!UserSetting::validateRadius($radius)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->editMessageTextOrSendMessage(
                    $this->render('radius-error')
                )
                ->merge($this->actionLocation(false))
                ->build();
        }

        $setting = $this->getTelegramUser()->getSetting(UserSetting::PLACE_AD_RADIUS);

        if (!isset($setting)) {
            $setting = new UserSetting();

            $setting->setAttributes([
                'user_id' => $this->getTelegramUser()->id,
                'setting' => UserSetting::PLACE_AD_RADIUS,
                'value' => $radius,
            ]);
        }

        $setting->value = $radius;
        $setting->save();

        return $this->actionPlace();
    }

    public function actionPlace()
    {
        $adsPost = new AdsPost();
        $user = $this->getTelegramUser();

        $adsPost->setAttributes([
            'user_id' => $this->getTelegramUser()->id,
            'title' => $user->getSetting(UserSetting::PLACE_AD_TITLE)->value,
            'description' => $user->getSetting(UserSetting::PLACE_AD_DESCRIPTION)->value,
            'photo_file_id' => $user->getSetting(UserSetting::PLACE_AD_PHOTO_FILE_ID)->value == UserSetting::NO_PHOTO_FILE_ID ? null : $user->getSetting(UserSetting::PLACE_AD_PHOTO_FILE_ID)->value,
            'currency_id' => intval($user->getSetting(UserSetting::PLACE_AD_CURRENCY_ID)->value),
            'price' => $user->getSetting(UserSetting::PLACE_AD_PRICE)->value,
            'delivery_km' => intval($user->getSetting(UserSetting::PLACE_AD_RADIUS)->value),
            'location_lat' => $user->getSetting(UserSetting::PLACE_AD_LOCATION_LAT)->value,
            'location_lon' => $user->getSetting(UserSetting::PLACE_AD_LOCATION_LON)->value,
            'category_id' => intval($user->getSetting(UserSetting::PLACE_AD_CATEGORY_ID)->value),
            'status' => AdsPost::STATUS_NOT_ACTIVATED,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $adsPost->save();

        foreach (UserSetting::find()->where([
            'and',
            ['user_id' => $this->getTelegramUser()->id],
            ['like', 'setting', 'place_ad_keyword_'],
        ])->all() as $adKeywordSetting) {
            $adKeyword = AdKeyword::findOne(intval($adKeywordSetting->value));

            $adsPost->link('keywords', $adKeyword);
        }

        if (($photoFileId = $user->getSetting(UserSetting::PLACE_AD_PHOTO_FILE_ID)->value) != UserSetting::NO_PHOTO_FILE_ID) {
            $adPhoto = new AdPhoto();

            $adPhoto->setAttributes([
                'ads_post_id' => $adsPost->id,
                'file_id' => $photoFileId,
            ]);
            $adPhoto->save();

            $adsPost->link('photos', $adPhoto);
        }

        return $this->actionPost($adsPost->id);
    }
}
