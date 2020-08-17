<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\crud\CrudController;
use app\behaviors\SetDefaultCurrencyBehavior;
use app\behaviors\SetAttributeValueBehavior;
use app\modules\bot\components\crud\rules\ExplodeStringFieldComponent;
use app\modules\bot\components\crud\rules\LocationToArrayFieldComponent;
use app\modules\bot\components\crud\services\IntermediateFieldService;
use app\modules\bot\components\crud\rules\PhotoFieldComponent;
use app\modules\bot\validators\RadiusValidator;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\models\AdKeyword;
use app\models\AdOfferKeyword;
use app\models\AdSection;
use app\models\AdOffer;
use app\models\AdOfferMatch;
use app\models\AdSearch;
use app\models\AdPhoto;
use app\modules\bot\models\User as TelegramUser;
use app\models\User;
use yii\base\DynamicModel;
use yii\base\ModelEvent;
use yii\data\Pagination;
use app\modules\bot\components\helpers\PaginationButtons;
use app\models\Currency;
use yii\db\ActiveRecord;

/**
 * Class SAdOfferController
 *
 * @package app\modules\bot\controllers\privates
 */
class SAdOfferController extends CrudController
{
    /** @inheritDoc */
    protected function rules()
    {
        return [
            [
                'model' => AdOffer::class,
                'prepareViewParams' => function ($params) {
                    $model = $params['model'] ?? null;

                    return [
                        'sectionName' => AdSection::getAdOfferName($model->section),
                        'section' => $model->section,
                        'keywords' => self::getKeywordsAsString($model->getKeywords()->all()),
                        'adOffer' => $model,
                        'currency' =>  isset($model->currency_id) ? Currency::findOne($model->currency_id) : null,
                        'locationLink' => ExternalLink::getOSMLink($model->location_lat, $model->location_lon),
                        'showDetailedInfo' => false,
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
                                'value' => AdOffer::STATUS_OFF,
                            ],
                        ],
                        'hidden' => true,
                    ],
                    'keywords' => [
                        //'enableAddButton' = true,
                        'isRequired' => false,
                        'relation' => [
                            'model' => AdOfferKeyword::class,
                            'attributes' => [
                                'ad_offer_id' => [AdOffer::class, 'id'],
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
                    'photo' => [
                        'isRequired' => false,
                        'relation' => [
                            'model' => AdPhoto::class,
                            'attributes' => [
                                'ad_offer_id' => [AdOffer::class, 'id', 'code'],
                                'file_id' => [DynamicModel::class, 'id', 'keyword'],
                            ],
                            'buttonFunction' => function ($params) { //pay attention. It inside relation attribute
                                $params['text'] = 'Uploaded photo';

                                return $params;
                            },
                        ],
                        'component' => [
                            'class' => PhotoFieldComponent::class,
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
                    'price' => [
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
                                'callback' => function (AdOffer $model) {
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
                    'delivery_radius' => [
                        'view' => 'edit-radius',
                        'buttons' => [
                            [
                                'text' => Yii::t('bot', 'NO'),
                                'callback' => function (AdOffer $model) {
                                    $model->delivery_radius = 0;

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

        $adOfferQuery = AdOffer::find()
            ->where([
                'user_id' => $user->id,
                'section' => $adSection,
            ])
            ->orderBy([
                'status' => SORT_DESC,
                'title' => SORT_ASC,
            ]);

        $adOfferCount = $adOfferQuery->count();

        $pagination = new Pagination([
            'totalCount' => $adOfferCount,
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $adOffers = $adOfferQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $buttons = array_map(function ($adOffer) {
            return [
                [
                    'text' => ($adOffer->isActive() ? '' : Emoji::INACTIVE . ' ') . $adOffer->title,
                    'callback_data' => self::createRoute('view', [
                        'adOfferId' => $adOffer->id,
                    ]),
                ],
            ];
        }, $adOffers);

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

        $matchesCount = AdOfferMatch::find()
            ->joinWith('adOffer')
            ->andWhere([
                AdOffer::tableName() . '.user_id' => $user->id,
                AdOffer::tableName() . '.section' => $adSection,
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
                'm' => $this->getModelName(AdOffer::class),
            ]),
            'text' => Emoji::ADD,
        ];

        $buttons[] = $rowButtons;

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'sectionName' => AdSection::getAdOfferName($adSection),
                ]),
                $buttons
            )
            ->build();
    }

    public function actionAdd($adSection)
    {
        $this->getState()->setName(self::createRoute('title-send'));
        $this->getState()->setIntermediateField('adOfferSection', $adSection);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-title'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'adSection' => $adSection,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionEdit($adOfferId)
    {
        $this->getState()->setName(null);
        $user = $this->getUser();

        $adOffer = $user->getAdOffers()
            ->where([
                'id' => $adOfferId,
            ])
            ->one();

        if (!isset($adOffer)) {
            return [];
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('show', [
                    'adOffer' => $adOffer,
                    'currency' => Currency::findOne($adOffer->currency_id),
                    'sectionName' => AdSection::getAdOfferName($adOffer->section),
                    'keywords' => self::getKeywordsAsString($adOffer->getKeywords()->all()),
                    'locationLink' => ExternalLink::getOSMLink($adOffer->location_lat, $adOffer->location_lon),
                    'showDetailedInfo' => false,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit-title', [
                                'adOfferId' => $adOfferId,
                            ]),
                            'text' => Yii::t('bot', 'Title'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-description', [
                                'adOfferId' => $adOfferId,
                            ]),
                            'text' => Yii::t('bot', 'Description'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-keywords', [
                                'adOfferId' => $adOfferId,
                            ]),
                            'text' => Yii::t('bot', 'Keywords'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-photo', [
                                'adOfferId' => $adOfferId,
                            ]),
                            'text' => Yii::t('bot', 'Photo'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-price', [
                                'adOfferId' => $adOfferId,
                            ]),
                            'text' => Yii::t('bot', 'Price'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-location', [
                                'adOfferId' => $adOfferId,
                            ]),
                            'text' => Yii::t('bot', 'Location'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('edit-radius', [
                                'adOfferId' => $adOfferId,
                            ]),
                            'text' => Yii::t('bot', 'Delivery radius'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('view', [
                                'adOfferId' => $adOfferId,
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

    public function actionView($adOfferId)
    {
        $this->getState()->setName(null);
        $user = $this->getUser();

        $adOffer = $user->getAdOffers()
            ->where([
                'user_id' => $user->id,
                'id' => $adOfferId,
            ])
            ->one();

        if (!isset($adOffer)) {
            return [];
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('status', [
                    'adOfferId' => $adOffer->id,
                ]),
                'text' => Yii::t('bot', 'Status') . ': ' . ($adOffer->isActive() ? 'ON' : 'OFF'),
            ]
        ];

        $matchesCount = $adOffer->getMatches()->count();

        if ($matchesCount) {
            $buttons[][] = [
                'callback_data' => self::createRoute('matches', [
                    'adOfferId' => $adOffer->id,
                ]),
                'text' => Emoji::OFFERS . ' ' . $matchesCount,
            ];
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('index', [
                    'adSection' => $adOffer->section,
                ]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'callback_data' => self::createRoute('u', [
                    'm' => $this->getModelName(AdOffer::class),
                    'i' => $adOffer->id,
                ]),
                'text' => Emoji::EDIT,
            ],
            [
                'callback_data' => self::createRoute('delete', [
                    'adOfferId' => $adOffer->id,
                ]),
                'text' => Emoji::DELETE,
            ],
        ];

        return $this->getResponseBuilder()
            ->sendPhotoOrEditMessageTextOrSendMessage(
                $adOffer->getPhotos()->count() ? $adOffer->getPhotos()->one()->file_id : null,
                $this->render('show', [
                    'adOffer' => $adOffer,
                    'currency' => Currency::findOne($adOffer->currency_id),
                    'sectionName' => AdSection::getAdOfferName($adOffer->section),
                    'keywords' => self::getKeywordsAsString($adOffer->getKeywords()->all()),
                    'locationLink' => ExternalLink::getOSMLink($adOffer->location_lat, $adOffer->location_lon),
                    'showDetailedInfo' => true,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    public function actionMatches($adOfferId, $page = 1)
    {
        $user = $this->getUser();

        $adOffer = $user->getAdOffers()
            ->where([
                'user_id' => $user->id,
                'id' => $adOfferId,
            ])
            ->one();

        if (!isset($adOffer)) {
            return [];
        }

        $matchesQuery = $adOffer->getMatches();
        $matchesCount = $matchesQuery->count();

        if (!$matchesCount) {
            return $this->actionView($adOfferId);
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

        $adSearch = $matchesQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->one();

        $buttons[] = [
            [
                'text' => $adOffer->title,
                'callback_data' => self::createRoute('view', [
                    'adOfferId' => $adOffer->id,
                ]),
            ]
        ];

        $buttons[] = PaginationButtons::build($pagination, function ($page) use ($adOfferId) {
            return self::createRoute('matches', [
                'adOfferId' => $adOfferId,
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'adOfferId' => $adOffer->id,
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
                $this->render('match', [
                    'sectionName' => AdSection::getAdSearchName($adSearch->section),
                    'adSearch' => $adSearch,
                    'user' => TelegramUser::findOne(['user_id' => $adSearch->user_id]),
                    'keywords' => self::getKeywordsAsString($adSearch->getKeywords()->all()),
                    'locationLink' => ExternalLink::getOSMLink($adSearch->location_lat, $adSearch->location_lon),
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

        $matchesQuery = AdOfferMatch::find()
            ->joinWith('adOffer')
            ->andWhere([
                AdOffer::tableName() . '.user_id' => $user->id,
                AdOffer::tableName() . '.section' => $adSection,
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

        $adOfferMatch = $matchesQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->one();
        $adOffer = $adOfferMatch->adOffer;
        $adSearch = $adOfferMatch->adSearch;

        $buttons[] = [
            [
                'text' => $adOffer->title,
                'callback_data' => self::createRoute('view', [
                    'adOfferId' => $adOffer->id,
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
            ->editMessageTextOrSendMessage(
                $this->render('match', [
                    'sectionName' => AdSection::getAdSearchName($adSearch->section),
                    'adSearch' => $adSearch,
                    'user' => TelegramUser::findOne(['user_id' => $adSearch->user_id]),
                    'keywords' => self::getKeywordsAsString($adSearch->getKeywords()->all()),
                    'locationLink' => ExternalLink::getOSMLink($adSearch->location_lat, $adSearch->location_lon),
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    public function actionEditTitle($adOfferId)
    {
        $this->getState()->setName(self::createRoute('new-title', [
            'adOfferId' => $adOfferId,
        ]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-title'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('u', [
                                'm' => $this->getModelName(AdOffer::class),
                                'i' => $adOfferId,
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

    public function actionNewTitle($adOfferId)
    {
        if ($this->getUpdate()->getMessage()) {
            $adOffer = AdOffer::findOne($adOfferId);

            $title = $this->getUpdate()->getMessage()->getText();

            $adOffer->setAttributes([
                'title' => $title,
            ]);
            $adOffer->save();

            return $this->actionView($adOfferId);
        }
    }

    public function actionEditDescription($adOfferId)
    {
        $this->getState()->setName(self::createRoute('new-description', [
            'adOfferId' => $adOfferId,
        ]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-description'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('new-description-skip', [
                                'adOfferId' => $adOfferId,
                            ]),
                            'text' => Yii::t('bot', 'NO'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('u', [
                                'm' => $this->getModelName(AdOffer::class),
                                'i' => $adOfferId,
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

    public function actionNewDescriptionSkip($adOfferId)
    {
        $adOffer = AdOffer::findOne($adOfferId);

        $adOffer->setAttributes([
            'description' => null,
        ]);
        $adOffer->save();

        return $this->actionView($adOfferId);
    }

    public function actionNewDescription($adOfferId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            $description = $message->getText();

            $adOffer = AdOffer::findOne($adOfferId);

            $adOffer->description = strval($description);

            $adOffer->save();

            return $this->actionView($adOfferId);
        }
    }

    public function actionEditPhoto($adOfferId)
    {
        $this->getState()->setName(self::createRoute('new-photo', [
            'adOfferId' => $adOfferId,
        ]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-photo'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('new-photo-skip', [
                                'adOfferId' => $adOfferId,
                            ]),
                            'text' => Yii::t('bot', 'NO'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('u', [
                                'm' => $this->getModelName(AdOffer::class),
                                'i' => $adOfferId,
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

    public function actionNewPhotoSkip($adOfferId)
    {
        $adOffer = AdOffer::findOne($adOfferId);

        $adOffer->unlinkAll('photos', true);

        return $this->actionView($adOfferId);
    }

    public function actionNewPhoto($adOfferId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getPhoto()) {
            $photoFileId = $message->getPhoto()[0]->getFileId();

            $adOffer = AdOffer::findOne($adOfferId);

            $adOffer->unlinkAll('photos', true);

            $adPhoto = new AdPhoto();

            $adPhoto->setAttributes([
                'ad_offer_id' => $adOffer->id,
                'file_id' => $photoFileId,
            ]);

            $adPhoto->save();

            $adOffer->link('photos', $adPhoto);

            return $this->actionView($adOfferId);
        } else {
            return $this->actionEditPhoto($adOfferId);
        }
    }

    public function actionEditKeywords($adOfferId)
    {
        $this->getState()->setName(self::createRoute('new-keywords', [
            'adOfferId' => $adOfferId,
        ]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-keywords'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('new-keywords-skip', [
                                'adOfferId' => $adOfferId,
                            ]),
                            'text' => Yii::t('bot', 'NO'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('u', [
                                'm' => $this->getModelName(AdOffer::class),
                                'i' => $adOfferId,
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

    public function actionNewKeywordsSkip($adOfferId)
    {
        $adOffer = AdOffer::findOne($adOfferId);

        $adOffer->unlinkAll('keywords', true);

        $adOffer->markToUpdateMatches();

        return $this->actionView($adOfferId);
    }

    public function actionNewKeywords($adOfferId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            $adOffer = AdOffer::findOne($adOfferId);

            $keywords = self::parseKeywords($message->getText());

            if (empty($keywords)) {
                return $this->actionEditKeywords($adOfferId);
            }

            $adOffer->unlinkAll('keywords', true);

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

                $adOffer->link('keywords', $adKeyword);
            }

            $adOffer->markToUpdateMatches();

            return $this->actionView($adOfferId);
        } else {
            return $this->actionEditKeyword($adOfferId);
        }
    }

    public function actionEditCurrency($adOfferId, $page = 1)
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
                        'adOfferId' => $adOfferId,
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
                'callback_data' => self::createRoute('new-currency', [
                    'adOfferId' => $adOfferId,
                    'currencyId' => $currency->id,
                ]),
                'text' => $currency->code . ' - ' . $currency->name,
            ];
        }

        $buttons[] = PaginationButtons::build($pagination, function ($page) use ($adOfferId) {
            return self::createRoute('edit-currency', [
                'adOfferId' => $adOfferId,
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'callback_data' => self::createRoute('edit-price', [
                    'adOfferId' => $adOfferId,
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

    public function actionNewCurrency($adOfferId, $currencyId)
    {
        $adOffer = AdOffer::findOne($adOfferId);

        if (isset($adOffer)) {
            $adOffer->setAttributes([
                'currency_id' => $currencyId,
            ]);
            $adOffer->save();
        }

        return $this->actionEditPrice($adOfferId);
    }

    public function actionEditPrice($adOfferId)
    {
        $this->getState()->setName(self::createRoute('new-price', [
            'adOfferId' => $adOfferId,
        ]));

        $adOffer = AdOffer::findOne($adOfferId);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-price', [
                    'currencyCode' => Currency::findOne($adOffer->currency_id)->code,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('edit-currency', [
                                'adOfferId' => $adOfferId,
                            ]),
                            'text' => Yii::t('bot', 'Edit currency'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('new-price-skip', [
                                'adOfferId' => $adOfferId,
                            ]),
                            'text' => Yii::t('bot', 'NO'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('u', [
                                'm' => $this->getModelName(AdOffer::class),
                                'i' => $adOfferId,
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

    public function actionNewPriceSkip($adOfferId)
    {
        $adOffer = AdOffer::findOne($adOfferId);

        $adOffer->setAttributes([
            'price' => null,
        ]);

        $adOffer->save();

        return $this->actionView($adOfferId);
    }

    public function actionNewPrice($adOfferId)
    {
        if ($message = $this->getUpdate()->getMessage()) {
            if (!AdOffer::validatePrice($message->getText())) {
                return $this->actionEditPrice($adOfferId);
            }

            $price = $message->getText();

            $adOffer = AdOffer::findOne($adOfferId);

            $adOffer->setAttributes([
                'price' => doubleval($price),
            ]);

            $adOffer->save();

            return $this->actionView($adOfferId);
        }
    }

    public function actionEditLocation($adOfferId)
    {
        $this->getState()->setName(self::createRoute('new-location-send', [
            'adOfferId' => $adOfferId,
        ]));

        $buttons = [];

        if ($this->getTelegramUser()->location_lat !== null && $this->getTelegramUser()->location_lon != null) {
            $buttons[][] = [
                'callback_data' => self::createRoute('new-location-my', [
                    'adOfferId' => $adOfferId,
                ]),
                'text' => Yii::t('bot', 'My location'),
            ];
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('u', [
                    'm' => $this->getModelName(AdOffer::class),
                    'i' => $adOfferId,
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
                $this->render('edit-location'),
                $buttons
            )
            ->build();
    }

    public function actionNewLocationMy($adOfferId)
    {
        $latitude = $this->getTelegramUser()->location_lat;
        $longitude = $this->getTelegramUser()->location_lon;

        return $this->actionNewLocation($adOfferId, $latitude, $longitude);
    }

    public function actionNewLocationSend($adOfferId)
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

        return $this->actionNewLocation($adOfferId, $latitude, $longitude);
    }

    public function actionNewLocation($adOfferId, $latitude, $longitude)
    {
        if ($latitude && $longitude) {
            $adOffer = AdOffer::findOne($adOfferId);

            $adOffer->setAttributes([
                'location_lat' => strval($latitude),
                'location_lon' => strval($longitude),
            ]);

            $adOffer->save();

            $adOffer->markToUpdateMatches();
        }

        return $this->actionView($adOfferId);
    }

    public function actionEditRadius($adOfferId)
    {
        $this->getState()->setName(self::createRoute('new-radius', [
            'adOfferId' => $adOfferId,
        ]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-radius'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('new-radius-skip', [
                                'adOfferId' => $adOfferId,
                            ]),
                            'text' => Yii::t('bot', 'No delivery'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('u', [
                                'm' => $this->getModelName(AdOffer::class),
                                'i' => $adOfferId,
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

    public function actionNewRadiusSkip($adOfferId)
    {
        $adOffer = AdOffer::findOne($adOfferId);

        $adOffer->setAttributes([
            'delivery_radius' => 0,
        ]);

        $adOffer->save();

        return $this->actionView($adOfferId);
    }

    public function actionNewRadius($adOfferId)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            if (!AdOffer::validateRadius($message->getText())) {
                return $this->actionEditRadius($adOfferId);
            }

            $adOffer = AdOffer::findOne($adOfferId);

            $deliveryRadius = min(RadiusValidator::MAX_RADIUS, intval($message->getText()));

            $adOffer->setAttributes([
                'delivery_radius' => $deliveryRadius,
            ]);

            $adOffer->save();

            $adOffer->markToUpdateMatches();

            return $this->actionView($adOfferId);
        }
    }

    public function actionStatus($adOfferId)
    {
        $adOffer = AdOffer::findOne($adOfferId);

        if ($adOffer->isActive()) {
            $adOffer->status = AdOffer::STATUS_OFF;
        } else {
            $adOffer->status = AdOffer::STATUS_ON;
        }

        $adOffer->save();

        if ($adOffer->isActive()) {
            $adOffer->markToUpdateMatches();
        } else {
            $adOffer->unlinkAll('matches', true);
            $adOffer->unlinkAll('counterMatches', true);

            $adOffer->setAttributes([
                'processed_at' => time(),
            ]);

            $adOffer->save();
        }

        return $this->actionView($adOfferId);
    }

    private static function getKeywordsAsString($adKeywords)
    {
        $keywords = [];

        foreach ($adKeywords as $adKeyword) {
            $keywords[] = $adKeyword->keyword;
        }

        return implode(', ', $keywords);
    }

    public function actionTitleSend()
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            $this->getState()->setIntermediateField('adOfferTitle', $message->getText());

            return $this->actionTitle();
        } else {
            return $this->actionAdd($this->getState()->getIntermediateField('adOfferSection'));
        }
    }

    public function actionTitle()
    {
        $this->getState()->setName(self::createRoute('description'));

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
                                'm' => $this->getModelName(AdOffer::class),
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

    public static function parseKeywords($text)
    {
        if (preg_match_all('/(^|[\.,\n])([^\.,\n]+)/', $text, $matches)) {
            return array_map('mb_strtolower', array_map('trim', $matches[2]));
        } else {
            return [];
        }
    }

    public function actionKeywordsSkip()
    {
        $this->getState()->setIntermediateFieldArray('adOfferKeywords', []);

        return $this->actionKeywords();
    }

    public function actionKeywords()
    {
        if ($message = $this->getUpdate()->getMessage()) {
            $keywords = self::parseKeywords($message->getText());

            if (empty($keywords)) {
                return $this->actionDescription();
            }

            $adOfferKeywords = [];
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

                $adOfferKeywords[] = $adKeyword->id;
            }

            $this->getState()->setIntermediateFieldArray('adOfferKeywords', $adOfferKeywords);
        }

        $this->getState()->setName(self::createRoute('photo-send'));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-photo'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('photo-skip'),
                            'text' => Yii::t('bot', 'SKIP'),
                        ],
                    ],
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

    public function actionDelete($adOfferId)
    {
        $user = $this->getUser();

        $adOffer = $user->getAdOffers()
            ->where([
                'id' => $adOfferId,
            ])
            ->one();

        if (!isset($adOffer)) {
            return [];
        }

        $adSection = $adOffer->section;

        $adOffer->unlinkAll('photos', true);
        $adOffer->unlinkAll('keywords', true);
        $adOffer->delete();

        return $this->actionIndex($adSection);
    }

    public function actionDescriptionSkip()
    {
        $this->getState()->setIntermediateField('adOfferDescription', null);

        return $this->actionDescription();
    }

    public function actionDescription()
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            $description = $message->getText();

            $this->getState()->setIntermediateField('adOfferDescription', $description);
        }

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

    public function actionPhotoSend($page = 1)
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getPhoto()) {
            $photoFileId = $message->getPhoto()[0]->getFileId();

            $this->getState()->setIntermediateField('adOfferPhotoFileId', $photoFileId);
        }

        return $this->actionPhoto($page);
    }

    public function actionPhotoSkip($page = 1)
    {
        $this->getState()->setIntermediateField('adOfferPhoto', null);

        return $this->actionPhoto($page);
    }

    public function actionPhoto($page = 1)
    {
        $this->getState()->setName(null);

        $telegramUser = $this->getTelegramUser();
        if ($telegramUser->user_id && User::findOne($telegramUser->user_id)) {
            $user = User::findOne($telegramUser->user_id);

            if ($user->currency_id !== null) {
                return $this->actionCurrency($user->currency_id);
            }
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

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-currency'),
                $buttons
            )
            ->build();
    }

    public function actionCurrency($currencyId = null)
    {
        if ($currencyId) {
            $this->getState()->setIntermediateField('adOfferCurrencyId', $currencyId);
        }

        $this->getState()->setName(self::createRoute('price'));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('edit-price', [
                    'currencyCode' => Currency::findOne($this->getState()->getIntermediateField('adOfferCurrencyId'))->code,
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
                            'callback_data' => self::createRoute('price-skip'),
                            'text' => Yii::t('bot', 'SKIP'),
                        ],
                    ],
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
                'callback_data' => self::createRoute('currency', [
                    'currencyId' => $currency->id,
                ]),
                'text' => $currency->code . ' - ' . $currency->name,
            ];
        }

        $buttons[] = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('change-currency', [
                'page' => $page,
            ]);
        });

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

    public function actionPriceSkip()
    {
        $this->getState()->setIntermediateField('adOfferPrice', null);

        return $this->actionPrice();
    }

    public function actionPrice()
    {
        if ($message = $this->getUpdate()->getMessage()) {
            if (!AdOffer::validatePrice($message->getText())) {
                return $this->actionCurrency();
            }

            $this->getState()->setIntermediateField('adOfferPrice', $message->getText());
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

        return $this->getResponseBuilder()
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
        } elseif ($message && $message->getText() && AdOffer::validateLocation($message->getText())) {
            $latitude = AdOffer::getLatitudeFromText($message->getText());
            $longitude = AdOffer::getLongitudeFromText($message->getText());
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
            $this->getState()->setIntermediateField('adOfferLocationLatitude', strval($latitude));
            $this->getState()->setIntermediateField('adOfferLocationLongitude', strval($longitude));
        }

        $this->getState()->setName(self::createRoute('radius-send'));

        return $this->getResponseBuilder()
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
        $this->getState()->setIntermediateField('adOfferDeliveryRadius', '0');

        return $this->actionRadius();
    }

    public function actionRadiusSend()
    {
        if (($message = $this->getUpdate()->getMessage()) && $this->getUpdate()->getMessage()->getText()) {
            if (!AdOffer::validateRadius($message->getText())) {
                return $this->actionLocation();
            }

            $radius = min(intval($message->getText()), RadiusValidator::MAX_RADIUS);
            $this->getState()->setIntermediateField('adOfferDeliveryRadius', $radius);

            return $this->actionRadius();
        }
    }

    public function actionRadius()
    {
        return $this->actionPlace();
    }

    public function actionPlace()
    {
        $adOffer = new AdOffer();
        $state = $this->getState();

        $adOffer->setAttributes([
            'user_id' => $this->getTelegramUser()->id,
            'title' => $state->getIntermediateField('adOfferTitle'),
            'description' => $state->getIntermediateField('adOfferDescription'),
            'currency_id' => $state->getIntermediateField('adOfferCurrencyId'),
            'price' => $state->getIntermediateField('adOfferPrice'),
            'delivery_radius' => doubleval($state->getIntermediateField('adOfferDeliveryRadius')),
            'location_lat' => $state->getIntermediateField('adOfferLocationLatitude'),
            'location_lon' => $state->getIntermediateField('adOfferLocationLongitude'),
            'section' => intval($state->getIntermediateField('adOfferSection')),
            'status' => AdOffer::STATUS_OFF,
            'created_at' => time(),
        ]);

        $adOffer->save();

        foreach ($this->getState()->getIntermediateFieldArray('adOfferKeywords') as $adKeywordId) {
            $adKeyword = AdKeyword::findOne($adKeywordId);

            $adOffer->link('keywords', $adKeyword);
        }

        if ($photoFileId = $this->getState()->getIntermediateField('adOfferPhotoFileId')) {
            $adPhoto = new AdPhoto();

            $adPhoto->setAttributes([
                'ad_offer_id' => $adOffer->id,
                'file_id' => $photoFileId,
            ]);
            $adPhoto->save();

            $adOffer->link('photos', $adPhoto);
        }

        return $this->actionView($adOffer->id);
    }
}
