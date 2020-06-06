<?php
namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\models\AdKeyword;
use app\modules\bot\models\AdOrder;
use app\modules\bot\models\AdCategory;
use app\modules\bot\models\AdSearch;
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

        $adOrderQuery = AdOrder::find()->where([
            'user_id' => $this->getTelegramUser()->id,
            'category_id' => $adCategoryId,
        ]);

        $adOrderCount = $adOrderQuery->count();

        $pagination = new Pagination([
            'totalCount' => $adOrderCount,
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        foreach ($adOrderQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all() as $adOrder) {
            $buttons[][] = [
                'text' => ($adOrder->isActive() ? '' : '❌ ') . $adOrder->title,
                'callback_data' => self::createRoute('post', ['adOrderId' => $adOrder->id]),
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

    public function actionEdit($adOrderId)
    {
        $adOrder = AdOrder::findOne($adOrderId);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('post', [
                    'adOrder' => $adOrder,
                    'currency' => Currency::findOne($adOrder->currency_id),
                    'categoryName' => AdCategory::getPlaceName($adOrder->category_id),
                    'keywords' => self::getKeywordsAsString($adOrder->getKeywords()->all()),
                    'locationLink' => ExternalLink::getOSMLink($adOrder->location_latitude, $adOrder->location_longitude),
                    'liveDays' => AdOrder::LIVE_DAYS,
                    'showDetailedInfo' => false,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit-title', ['adOrderId' => $adOrderId]),
                            'text' => Yii::t('bot', 'Title'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-keywords', ['adOrderId' => $adOrderId]),
                            'text' => Yii::t('bot', 'Keywords'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-description', ['adOrderId' => $adOrderId]),
                            'text' => Yii::t('bot', 'Description'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-photo', ['adOrderId' => $adOrderId]),
                            'text' => Yii::t('bot', 'Photo'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-price', ['adOrderId' => $adOrderId]),
                            'text' => Yii::t('bot', 'Price'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-location', ['adOrderId' => $adOrderId]),
                            'text' => Yii::t('bot', 'Location'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-radius', ['adOrderId' => $adOrderId]),
                            'text' => Yii::t('bot', 'Delivery radius'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('post', ['adOrderId' => $adOrderId]),
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

    public function actionPost($adOrderId)
    {
        $this->updatePost($adOrderId);

        $adOrder = AdOrder::findOne($adOrderId);

        $this->getState()->setName(null);

        $buttons = [];

        $buttons[][] = [
            'callback_data' => self::createRoute('status', ['adOrderId' => $adOrderId]),
            'text' => 'Status: ' . ($adOrder->isActive() ? 'ON' : 'OFF'),
        ];

        $matchedAdSearchesCount = $adOrder->getMatches()->count();

        if ($matchedAdSearchesCount > 0) {
            $buttons[][] = [
                'callback_data' => self::createRoute('matched-ad-searches', ['adOrderId' => $adOrderId]),
                'text' => '🙋‍♂️ ' . $matchedAdSearchesCount,
            ];
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('index', ['adCategoryId' => $adOrder->category_id]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'callback_data' => self::createRoute('edit', ['adOrderId' => $adOrderId]),
                'text' => Emoji::EDIT,
            ],
            [
                'callback_data' => self::createRoute('confirm-delete', ['adOrderId' => $adOrderId]),
                'text' => Emoji::DELETE,
            ],
        ];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->sendPhotoOrEditMessageTextOrSendMessage(
                $adOrder->getPhotos()->count() ? $adOrder->getPhotos()->one()->file_id : null,
                $this->render('post', [
                    'adOrder' => $adOrder,
                    'currency' => Currency::findOne($adOrder->currency_id),
                    'categoryName' => AdCategory::getPlaceName($adOrder->category_id),
                    'keywords' => self::getKeywordsAsString($adOrder->getKeywords()->all()),
                    'locationLink' => ExternalLink::getOSMLink($adOrder->location_latitude, $adOrder->location_longitude),
                    'liveDays' => AdOrder::LIVE_DAYS,
                    'showDetailedInfo' => true,
                ]),
                $buttons,
                true
            )
            ->build();
    }

    public function updatePost($adOrderId)
    {
        $adOrder = AdOrder::findOne($adOrderId);

        $adOrder->setAttributes([
            'renewed_at' => time(),
        ]);
        $adOrder->save();
    }

    public function actionMatchedAdSearches($adOrderId, $page = 1)
    {
        $adOrder = AdOrder::findOne($adOrderId);

        $matchedAdSearchesQuery = $adOrder->getMatches();

        if ($matchedAdSearchesQuery->count() == 0) {
            return $this->actionPost($adOrderId);
        }

        $pagination = new Pagination([
            'totalCount' => $matchedAdSearchesQuery->count(),
            'pageSize' => 1,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $buttons = [];

        $buttons[] = PaginationButtons::build($pagination, function ($page) use ($adOrderId) {
            return self::createRoute('matched-ad-searches', [
                'adOrderId' => $adOrderId,
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'callback_data' => self::createRoute('post', ['adOrderId' => $adOrderId]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        $matchedAdSearch = $matchedAdSearchesQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all()[0];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('matched-ad-searches', [
                    'categoryName' => AdCategory::getFindName($matchedAdSearch->category_id),
                    'adSearch' => $matchedAdSearch,
                    'user' => TelegramUser::findOne($matchedAdSearch->user_id),
                    'keywords' => self::getKeywordsAsString($matchedAdSearch->getKeywords()->all()),
                    'locationLink' => ExternalLink::getOSMLink($matchedAdSearch->location_latitude, $matchedAdSearch->location_longitude),
                ]),
                $buttons,
                true
            )
            ->build();
    }

    public function actionEditTitle($adOrderId)
    {
        $this->getState()->setName(self::createRoute('new-title', ['adOrderId' => $adOrderId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-title'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit', ['adOrderId' => $adOrderId]),
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

    public function actionNewTitle($adOrderId)
    {
        if ($this->getUpdate()->getMessage()) {
            $adOrder = AdOrder::findOne($adOrderId);

            $title = $this->getUpdate()->getMessage()->getText();

            $adOrder->setAttributes([
                'title' => $title,
            ]);
            $adOrder->save();

            return $this->actionPost($adOrderId);
        }
    }

    public function actionEditDescription($adOrderId)
    {
        $this->getState()->setName(self::createRoute('new-description', ['adOrderId' => $adOrderId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-description'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit', ['adOrderId' => $adOrderId]),
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

    public function actionNewDescription($adOrderId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            $description = $message->getText();

            $adOrder = AdOrder::findOne($adOrderId);

            $adOrder->description = strval($description);

            $adOrder->save();

            return $this->actionPost($adOrderId);
        }
    }

    public function actionEditPhoto($adOrderId)
    {
        $this->getState()->setName(self::createRoute('new-photo', ['adOrderId' => $adOrderId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-photo'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit', ['adOrderId' => $adOrderId]),
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

    public function actionNewPhoto($adOrderId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getPhoto()) {
            $photoFileId = $message->getPhoto()[0]->getFileId();

            $adOrder = AdOrder::findOne($adOrderId);

            $adOrder->unlinkAll('photos', true);

            $adPhoto = new AdPhoto();

            $adPhoto->setAttributes([
                'ad_order_id' => $adOrder->id,
                'file_id' => $photoFileId,
            ]);
            $adPhoto->save();

            $adOrder->link('photos', $adPhoto);

            return $this->actionPost($adOrderId);
        } else {
            return $this->actionEditPhoto($adOrderId);
        }
    }

    public function actionEditKeywords($adOrderId)
    {
        $this->getState()->setName(self::createRoute('new-keywords', ['adOrderId' => $adOrderId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-keywords'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit', ['adOrderId' => $adOrderId]),
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

    public function actionNewKeywords($adOrderId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            $adOrder = AdOrder::findOne($adOrderId);

            $keywords = self::parseKeywords($this->getUpdate()->getMessage()->getText());

            if (empty($keywords)) {
                return $this->actionEditKeywords($adOrderId);
            }

            $adOrder->unlinkAll('keywords', true);

            foreach ($keywords as $keyword) {
                $adKeyword = AdKeyword::find()->where(['keyword' => $keyword])->one();

                if (!isset($adKeyword)) {
                    $adKeyword = new AdKeyword();

                    $adKeyword->setAttributes([
                        'keyword' => $keyword,
                    ]);

                    $adKeyword->save();
                }

                $adOrder->link('keywords', $adKeyword);
            }

            $adOrder->markToUpdateMatches();

            return $this->actionPost($adOrderId);
        } else {
            return $this->actionEditKeyword($adOrderId);
        }
    }

    public function actionEditCurrency($adOrderId, $page = 1)
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
                        'adOrderId' => $adOrderId,
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
                    'adOrderId' => $adOrderId,
                    'currencyId' => $currency->id,
                ]),
                'text' => $currency->code . ' - ' . $currency->name,
            ];
        }

        $buttons[] = PaginationButtons::build($pagination, function ($page) use ($adOrderId) {
            return self::createRoute('edit-currency', [
                'adOrderId' => $adOrderId,
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'callback_data' => self::createRoute('edit-price', ['adOrderId' => $adOrderId]),
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

    public function actionNewCurrency($adOrderId, $currencyId)
    {
        $adOrder = AdOrder::findOne($adOrderId);

        if (isset($adOrder)) {
            $adOrder->setAttributes([
                'currency_id' => $currencyId,
            ]);
            $adOrder->save();
        }

        return $this->actionEditPrice($adOrderId);
    }

    public function actionEditPrice($adOrderId)
    {
        $this->getState()->setName(self::createRoute('new-price', ['adOrderId' => $adOrderId]));

        $adOrder = AdOrder::findOne($adOrderId);
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-price', [
                    'currencyCode' => Currency::findOne($adOrder->currency_id)->code
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit-currency', ['adOrderId' => $adOrderId]),
                            'text' => Yii::t('bot', 'Edit currency'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit', ['adOrderId' => $adOrderId]),
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

    public function actionNewPrice($adOrderId)
    {
        if ($message = $this->getUpdate()->getMessage()) {
            if (!AdOrder::validatePrice($message->getText())) {
                return $this->actionEditPrice($adOrderId);
            }

            $price = $message->getText();

            $adOrder = AdOrder::findOne($adOrderId);

            $adOrder->setAttributes([
                'price' => intval(100.0 * doubleval($price)),
            ]);

            $adOrder->save();

            return $this->actionPost($adOrderId);
        }
    }

    public function actionEditLocation($adOrderId)
    {
        $this->getState()->setName(self::createRoute('new-location-send', ['adOrderId' => $adOrderId]));

        $buttons = [];

        if ($this->getTelegramUser()->location_lat !== null && $this->getTelegramUser()->location_lon != null) {
            $buttons[][] = [
                'callback_data' => self::createRoute('new-location-my', ['adOrderId' => $adOrderId]),
                'text' => Yii::t('bot', 'My location'),
            ];
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('edit', ['adOrderId' => $adOrderId]),
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

    public function actionNewLocationMy($adOrderId)
    {
        $latitude = $this->getTelegramUser()->location_lat;
        $longitude = $this->getTelegramUser()->location_lon;

        return $this->actionNewLocation($adOrderId, $latitude, $longitude);
    }

    public function actionNewLocationSend($adOrderId)
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

        return $this->actionNewLocation($adOrderId, $latitude, $longitude);
    }

    public function actionNewLocation($adOrderId, $latitude, $longitude)
    {
        if ($latitude && $longitude) {
            $adOrder = AdOrder::findOne($adOrderId);

            $adOrder->setAttributes([
                'location_latitude' => strval($latitude),
                'location_longitude' => strval($longitude),
            ]);

            $adOrder->save();
            $adOrder->markToUpdateMatches();
        }

        return $this->actionPost($adOrderId);
    }

    public function actionEditRadius($adOrderId)
    {
        $this->getState()->setName(self::createRoute('new-radius', ['adOrderId' => $adOrderId]));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('edit-radius'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit', ['adOrderId' => $adOrderId]),
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

    public function actionNewRadius($adOrderId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            if (!AdOrder::validateRadius($message->getText())) {
                return $this->actionEditRadius($adOrderId);
            }

            $adOrder = AdOrder::findOne($adOrderId);

            $deliveryKm = $message->getText();

            $adOrder->setAttributes([
                'delivery_radius' => $deliveryKm,
            ]);

            $adOrder->save();
            $adOrder->markToUpdateMatches();

            return $this->actionPost($adOrderId);
        }
    }

    public function actionStatus($adOrderId)
    {
        $adOrder = AdOrder::findOne($adOrderId);

        if ($adOrder->isActive()) {
            $adOrder->status = AdOrder::STATUS_NOT_ACTIVATED;
        } else {
            $adOrder->status = AdOrder::STATUS_ACTIVATED;
        }

        $adOrder->save();

        if ($adOrder->isActive()) {
            $adOrder->markToUpdateMatches();
        } else {
            $adOrder->unlinkAll('matches', true);
            $adOrder->setAttributes([
                'edited_at' => null,
            ]);
            $adOrder->save();
        }

        return $this->actionPost($adOrderId);
    }

    private static function getKeywordsAsString($adKeywords)
    {
        $keywords = [];

        foreach ($adKeywords as $adKeyword) {
            $keywords[] = $adKeyword->keyword;
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
            foreach ($keywords as $index => $keyword) {
                $adKeyword = AdKeyword::find()->where(['keyword' => $keyword])->one();

                if (!isset($adKeyword)) {
                    $adKeyword = new AdKeyword();

                    $adKeyword->setAttributes([
                        'keyword' => $keyword,
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

    public function actionConfirmDelete($adOrderId)
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('confirm-delete'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('post', ['adOrderId' => $adOrderId]),
                            'text' => '❌',
                        ],
                        [
                            'callback_data' => self::createRoute('delete', ['adOrderId' => $adOrderId]),
                            'text' => '✅',
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionDelete($adOrderId)
    {
        $adCategoryId = AdOrder::findOne($adOrderId)->category_id;

        AdOrder::findOne($adOrderId)->unlinkAll('keywords', true);
        AdOrder::findOne($adOrderId)->delete();

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
            if (!AdOrder::validatePrice($message->getText())) {
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
        } elseif ($message && $message->getText() && AdOrder::validateLocation($message->getText())) {
            $latitude = AdOrder::getLatitudeFromText($message->getText());
            $longitude = AdOrder::getLongitudeFromText($message->getText());
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
        $this->getState()->setIntermediateField('placeAdDeliveryRadius', '0');

        return $this->actionRadius();
    }

    public function actionRadiusSend()
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            if (!AdOrder::validateRadius($message->getText())) {
                return $this->actionLocation();
            }

            $radius = $message->getText();
            $this->getState()->setIntermediateField('placeAdDeliveryRadius', $radius);

            return $this->actionRadius();
        }
    }

    public function actionRadius()
    {
        return $this->actionPlace();
    }

    public function actionPlace() 
    {
        $adOrder = new AdOrder();
        $state = $this->getState();
        
        $adOrder->setAttributes([
            'user_id' => $this->getTelegramUser()->id,
            'title' => $state->getIntermediateField('placeAdTitle'),
            'description' => $state->getIntermediateField('placeAdDescription'),
            'currency_id' => $state->getIntermediateField('placeAdCurrencyId'),
            'price' => $state->getIntermediateField('placeAdPrice'),
            'delivery_radius' => intval($state->getIntermediateField('placeAdDeliveryRadius')),
            'location_latitude' => $state->getIntermediateField('placeAdLocationLatitude'),
            'location_longitude' => $state->getIntermediateField('placeAdLocationLongitude'),
            'category_id' => intval($state->getIntermediateField('placeAdCategoryId')),
            'status' => AdOrder::STATUS_NOT_ACTIVATED,
            'created_at' => time(),
            'renewed_at' => time(),
            'edited_at' => time(),
        ]);

        $adOrder->save();

        foreach ($this->getState()->getIntermediateFieldArray('placeAdKeywords') as $adKeywordId) {
            $adKeyword = AdKeyword::findOne($adKeywordId);

            $adOrder->link('keywords', $adKeyword);
        }

        if ($photoFileId = $this->getState()->getIntermediateField('placeAdPhotoFileId')) {
            $adPhoto = new AdPhoto();

            $adPhoto->setAttributes([
                'ad_order_id' => $adOrder->id,
                'file_id' => $photoFileId,
            ]);
            $adPhoto->save();

            $adOrder->link('photos', $adPhoto);
        }

        return $this->actionPost($adOrder->id);
    }
}
