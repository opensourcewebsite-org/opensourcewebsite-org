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
    protected $updateAttributes = [
        'title',
        'description',
        'keywords',
        'max_price',
        'location',
        'pickup_radius',
    ];

    /**
     * {@inheritdoc}
     */
    protected function rules()
    {
        return [
            'model' => AdSearch::class,
            'prepareViewParams' => function ($params) {
                $model = $params['model'] ?? null;

                return [
                    'model' => $model,
                    'keywords' => self::getKeywordsAsString($model->getKeywords()->all()),
                    'locationLink' => ExternalLink::getOSMLink($model->location_lat, $model->location_lon),
                ];
            },
            'attributes' => [
                'title' => [],
                'description' => [
                    'isRequired' => false,
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
                ],
                'location' => [
                    'component' => LocationToArrayFieldComponent::class,
                    'buttons' => [
                        [
                            'hideCondition' => !$this->getTelegramUser()->location_lat || !$this->getTelegramUser()->location_lon,
                            'text' => Yii::t('bot', 'MY LOCATION'),
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
            ],
        ];
    }

    /**
     * @param int $adSection
     * @param int $page
     *
     * @return array
     */
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

        $adSearchesCount = $adSearchQuery->count();

        $pagination = new Pagination([
            'totalCount' => $adSearchesCount,
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $adSearches = $adSearchQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $buttons = array_map(function ($adSearch) {
            return [
                [
                    'text' => ($adSearch->isActive() ? '' : Emoji::INACTIVE . ' ') . $adSearch->title,
                    'callback_data' => self::createRoute('view', [
                        'id' => $adSearch->id,
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

    /**
     * @param int $id
     *
     * @return array
     */
    public function actionView($id)
    {
        $this->getState()->setName(null);
        $user = $this->getUser();

        $adSearch = $user->getAdSearches()
            ->where([
                'user_id' => $user->id,
                'id' => $id,
            ])
            ->one();

        if (!isset($adSearch)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $buttons[] = [
            [
                'text' => $adSearch->isActive() ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                'callback_data' => self::createRoute('set-status', [
                    'id' => $adSearch->id,
                ]),
            ]
        ];

        $matchesCount = $adSearch->getMatches()->count();

        if ($matchesCount) {
            $buttons[][] = [
                'callback_data' => self::createRoute('matches', [
                    'id' => $adSearch->id,
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
                'callback_data' => self::createRoute('update', [
                    'id' => $adSearch->id,
                ]),
                'text' => Emoji::EDIT,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('view', [
                    'model' => $adSearch,
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

    /**
     * @param int $id
     *
     * @return array
     */
    public function actionSetStatus($id)
    {
        $user = $this->getUser();

        $adSearch = $user->getAdSearches()
            ->where([
                'id' => $id,
            ])
            ->one();

        if (!isset($adSearch)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->backRoute->make('view', compact('id'));
        $this->endRoute->make('view', compact('id'));

        $adSearch->setAttributes([
            'status' => ($adSearch->isActive() ? AdSearch::STATUS_OFF : AdSearch::STATUS_ON),
        ]);

        $adSearch->save();

        return $this->actionView($adSearch->id);
    }

    /**
     * {@inheritdoc}
     */
    public function actionMatches($id, $page = 1)
    {
        $user = $this->getUser();

        $adSearch = $user->getAdSearches()
            ->where([
                'user_id' => $user->id,
                'id' => $id,
            ])
            ->one();

        if (!isset($adSearch)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
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
                    'id' => $adSearch->id,
                ]),
            ]
        ];

        $buttons[] = PaginationButtons::build($pagination, function ($page) use ($id) {
            return self::createRoute('matches', [
                'id' => $id,
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'id' => $adSearch->id,
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
                    'model' => $adOffer,
                    'user' => TelegramUser::findOne(['user_id' => $adOffer->user_id]),
                    'keywords' => self::getKeywordsAsString($adOffer->getKeywords()->all()),
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * {@inheritdoc}
     */
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
                    'id' => $adSearch->id,
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
                    'model' => $adOffer,
                    'user' => TelegramUser::findOne(['user_id' => $adOffer->user_id]),
                    'keywords' => self::getKeywordsAsString($adOffer->getKeywords()->all()),
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @param int $id
     */
    public function actionDelete($id)
    {
        $user = $this->getUser();

        $adSearch = $user->getAdSearches()
            ->where([
                'id' => $id,
            ])
            ->one();

        if (!isset($adSearch)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $adSection = $adSearch->section;

        $adSearch->unlinkAll('keywords', true);
        $adSearch->delete();

        return $this->actionIndex($adSection);
    }
}
