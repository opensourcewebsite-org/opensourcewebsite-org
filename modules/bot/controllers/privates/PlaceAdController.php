<?php
namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
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

        $this->getState()->setIntermediateField('placeAdCategoryId', $adCategoryId);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-title'),
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
        $adsPost = AdsPost::findOne($adsPostId);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('post', [
                    'adsPost' => $adsPost,
                    'currency' => Currency::findOne($adsPost->currency_id),
                    'categoryName' => AdCategory::getPlaceName($adsPost->category_id),
                    'keywords' => self::getKeywordsAsString($adsPost->getKeywords()->all()),
                    'locationLink' => ExternalLink::getOSMLink($adsPost->location_lat, $adsPost->location_lon),
                    'liveDays' => AdsPost::LIVE_DAYS,
                    'showDetailedInfo' => false,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit-title', ['adsPostId' => $adsPostId]),
                            'text' => Yii::t('bot', 'Title'),
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
                            'callback_data' => self::createRoute('edit-description', ['adsPostId' => $adsPostId]),
                            'text' => Yii::t('bot', 'Description'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-photo', ['adsPostId' => $adsPostId]),
                            'text' => Yii::t('bot', 'Photo'),
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
                ],
                true
            )
            ->build();
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

        $matchedPostSearchesCount = $adsPost->getMatches()->count();

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
                    'showDetailedInfo' => true,
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

        $matchedPostSearchesQuery = $adsPost->getMatches();

        if ($matchedPostSearchesQuery->count() == 0) {
            return $this->actionPost($adsPostId);
        }

        $pagination = new Pagination([
            'totalCount' => $matchedPostSearchesQuery->count(),
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

        $matchedPostSearch = $matchedPostSearchesQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all()[0];

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

    public function actionNewDescription($adsPostId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            $description = $message->getText();

            $adsPost = AdsPost::findOne($adsPostId);

            $adsPost->description = strval($description);

            $adsPost->save();

            return $this->actionPost($adsPostId);
        }
    }

    public function actionEditPhoto($adsPostId)
    {
        $this->getState()->setName(self::createRoute('new-photo', ['adsPostId' => $adsPostId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-photo'),
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

    public function actionNewPhoto($adsPostId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getPhoto()) {
            $photoFileId = $message->getPhoto()[0]->getFileId();

            $adsPost = AdsPost::findOne($adsPostId);

            $adsPost->unlinkAll('photos', true);

            $adPhoto = new AdPhoto();

            $adPhoto->setAttributes([
                'ads_post_id' => $adsPost->id,
                'file_id' => $photoFileId,
            ]);
            $adPhoto->save();

            $adsPost->link('photos', $adPhoto);

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

    public function actionNewKeywords($adsPostId)
    {
        if ($this->getUpdate()->getMessage()) {
            $adsPost = AdsPost::findOne($adsPostId);

            $keywords = self::parseKeywords($this->getUpdate()->getMessage()->getText());

            if (empty($keywords)) {
                return $this->actionEditKeywords($adsPostId);
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

            $adsPost->markToUpdateMatches();

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
                'callback_data' => self::createRoute('edit-price', ['adsPostId' => $adsPostId]),
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

        return $this->actionEditPrice($adsPostId);
    }

    public function actionEditPrice($adsPostId)
    {
        $this->getState()->setName(self::createRoute('new-price', ['adsPostId' => $adsPostId]));

        $adsPost = AdsPost::findOne($adsPostId);
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-price', [
                    'currencyCode' => Currency::findOne($adsPost->currency_id)->code
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit-currency', ['adsPostId' => $adsPostId]),
                            'text' => Yii::t('bot', 'Edit currency'),
                        ],
                    ],
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

    public function actionNewPrice($adsPostId)
    {
        if ($message = $this->getUpdate()->getMessage()) {
            if (!AdsPost::validatePrice($message->getText())) {
                return $this->actionEditPrice($adsPostId);
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
        $this->getState()->setName(self::createRoute('new-location-send', ['adsPostId' => $adsPostId]));

        $buttons = [];

        if ($this->getTelegramUser()->location_lat !== null && $this->getTelegramUser()->location_lon != null) {
            $buttons[][] = [
                'callback_data' => self::createRoute('new-location-my', ['adsPostId' => $adsPostId]),
                'text' => Yii::t('bot', 'My location'),
            ];
        }

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
                $this->render('edit-location'),
                $buttons
            )
            ->build();
    }

    public function actionNewLocationMy($adsPostId)
    {
        $latitude = $this->getTelegramUser()->location_lat;
        $longitude = $this->getTelegramUser()->location_lon;

        return $this->actionNewLocation($adsPostId, $latitude, $longitude);
    }

    public function actionNewLocationSend($adsPostId)
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

        return $this->actionNewLocation($adsPostId, $latitude, $longitude);
    }

    public function actionNewLocation($adsPostId, $latitude, $longitude)
    {
        if ($latitude && $longitude) {
            $adsPost = AdsPost::findOne($adsPostId);

            $adsPost->setAttributes([
                'location_lat' => strval($latitude),
                'location_lon' => strval($longitude),
            ]);

            $adsPost->save();
            $adsPost->markToUpdateMatches();
        }

        return $this->actionPost($adsPostId);
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

    public function actionNewRadius($adsPostId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            if (!AdsPost::validateRadius($message->getText())) {
                return $this->actionEditRadius($adsPostId);
            }

            $adsPost = AdsPost::findOne($adsPostId);

            $deliveryKm = $message->getText();

            $adsPost->setAttributes([
                'delivery_km' => $deliveryKm,
            ]);

            $adsPost->save();
            $adsPost->markToUpdateMatches();

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

        if ($adsPost->isActive()) {
            $adsPost->markToUpdateMatches();
        } else {
            $adsPost->unlinkAll('matches', true);
            $adsPost->setAttributes([
                'edited_at' => null,
            ]);
            $adsPost->save();
        }

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
        if ($update && ($message = $this->getUpdate()->getMessage())) {
            $this->getState()->setIntermediateField('placeAdTitle', $message->getText());
        }

        $this->getState()->setName(self::createRoute('keywords'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-keywords'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('add', [
                                'adCategoryId' => $this->getState()->getIntermediateField('placeAdCategoryId'),
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
        if ($message = $this->getUpdate()->getMessage()) {
            $keywords = self::parseKeywords($message->getText());

            if (empty($keywords)) {
                return $this->actionTitle(false);
            }

            $placeAdKeywords = [];
            foreach ($keywords as $index => $word) {
                $adKeyword = AdKeyword::find()->where(['word' => $word])->one();

                if (!isset($adKeyword)) {
                    $adKeyword = new AdKeyword();

                    $adKeyword->setAttributes([
                        'word' => $word,
                    ]);
                    $adKeyword->save();
                }

                $placeAdKeywords[] = $adKeyword->id;
            }

            $this->getState()->setIntermediateFieldArray('placeAdKeywords', $placeAdKeywords);
        }

        $this->getState()->setName(self::createRoute('description'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-description'),
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
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            $description = $message->getText();

            $this->getState()->setIntermediateField('placeAdDescription', $description);
        }

        $this->getState()->setName(self::createRoute('photo-send'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-photo'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('photo-skip'),
                            'text' => Yii::t('bot', 'Skip'),
                        ],
                    ],
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

    public function actionPhotoSend($page = 1)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getPhoto()) {
            $photoFileId = $message->getPhoto()[0]->getFileId();

            $this->getState()->setIntermdeiateField('placeAdPhotoFileId', $photoFileId);
        }

        return $this->actionPhoto($page);
    }

    public function actionPhotoSkip($page = 1)
    {
        $this->getState()->setIntermediateField('placeAdPhoto', null);

        return $this->actionPhoto($page);
    }

    public function actionPhoto($page = 1)
    {
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
            return self::createRoute('photo', ['page' => $page]);
        });

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

    public function actionCurrency($currencyId = null)
    {
        if ($currencyId) {
            $this->getState()->setIntermediateField('placeAdCurrencyId', $currencyId);
        }

        $this->getState()->setName(self::createRoute('price'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-price', [
                    'currencyCode' => Currency::findOne(
                        $this->getState()->getIntermediateField('placeAdCurrencyId'),
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

    public function actionPrice()
    {
        if ($message = $this->getUpdate()->getMessage()) {
            if (!AdsPost::validatePrice($message->getText())) {
                return $this->actionCurrency();
            }

            $this->getState()->setIntermediateField('placeAdPrice', strval(100.0 * doubleval($message->getText())));
        }

        $this->getState()->setName(self::createRoute('location-send'));

        $buttons = [];

        if ($this->getTelegramUser()->location_lat !== null && $this->getTelegramUser()->location_lon != null) {
            $buttons[][] = [
                'callback_data' => self::createRoute('location-set-my'),
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
                $this->render('edit-location'),
                $buttons
            )
            ->build();
    }

    public function actionLocationSetMy()
    {
        $latitude = $this->getTelegramUser()->location_lat;
        $longitude = $this->getTelegramUser()->location_lon;

        return $this->actionLocation($latitude, $longitude);
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

        return $this->actionLocation($latitude, $longitude);
    }

    public function actionLocation($latitude = null, $longitude = null)
    {
        $message = $this->getUpdate()->getMessage();
        

        if ($latitude && $longitude) {
            $this->getState()->setIntermediateField('placeAdLocationLatitude', strval($latitude));
            $this->getState()->setIntermediateField('placeAdLocationLongitude', strval($longitude));
        }

        $this->getState()->setName(self::createRoute('radius-send'));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-radius'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('radius-skip'),
                            'text' => Yii::t('bot', 'No delivery'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('price'),
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
        $this->getState()->setIntermediateField('placeAdDeliveryKm', '0');

        return $this->actionRadius();
    }

    public function actionRadiusSend()
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            if (!AdsPost::validateRadius($message->getText())) {
                return $this->actionLocation();
            }

            $radius = $message->getText();
            $this->getState()->setIntermediateField('placeAdDeliveryKm', $radius);

            return $this->actionRadius();
        }
    }

    public function actionRadius()
    {
        return $this->actionPlace();
    }

    public function actionPlace() 
    {
        $adsPost = new AdsPost();
        $state = $this->getState();
        
        $adsPost->setAttributes([
            'user_id' => $this->getTelegramUser()->id,
            'title' => $state->getIntermediateField('placeAdTitle'),
            'description' => $state->getIntermediateField('placeAdDescription'),
            'currency_id' => $state->getIntermediateField('placeAdCurrencyId'),
            'price' => $state->getIntermediateField('placeAdPrice'),
            'delivery_km' => intval($state->getIntermediateField('placeAdDeliveryKm')),
            'location_lat' => $state->getIntermediateField('placeAdLocationLatitude'),
            'location_lon' => $state->getIntermediateField('placeAdLocationLongitude'),
            'category_id' => intval($state->getIntermediateField('placeAdCategoryId')),
            'status' => AdsPost::STATUS_NOT_ACTIVATED,
            'created_at' => time(),
            'updated_at' => time(),
            'edited_at' => time(),
        ]);

        $adsPost->save();

        foreach ($this->getState()->getIntermediateFieldArray('placeAdKeywords') as $adKeywordId) {
            $adKeyword = AdKeyword::findOne($adKeywordId);

            $adsPost->link('keywords', $adKeyword);
        }

        if ($photoFileId = $this->getState()->getIntermediateField('placeAdPhotoFileId')) {
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
