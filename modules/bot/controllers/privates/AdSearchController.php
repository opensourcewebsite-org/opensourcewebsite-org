<?php

namespace app\modules\bot\controllers\privates;

use app\behaviors\SetAttributeValueBehavior;
use app\behaviors\SetDefaultCurrencyBehavior;
use app\models\AdKeyword;
use app\models\AdOffer;
use app\models\AdSearch;
use app\models\AdSearchKeyword;
use app\models\AdSearchMatch;
use app\models\AdSection;
use app\models\Currency;
use app\models\scenarios\AdSearch\SetActiveScenario;
use app\models\User;
use app\modules\bot\components\crud\CrudController;
use app\modules\bot\components\crud\rules\ExplodeStringFieldComponent;
use app\modules\bot\components\crud\rules\LocationToArrayFieldComponent;
use app\modules\bot\components\crud\services\IntermediateFieldService;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\User as TelegramUser;
use app\modules\bot\validators\RadiusValidator;
use Yii;
use yii\base\ModelEvent;
use yii\data\Pagination;
use yii\db\ActiveRecord;

/**
 * Class AdSearchController
 *
 * @link https://opensourcewebsite.org/ad-search
 * @package app\modules\bot\controllers\privates
 */
class AdSearchController extends CrudController
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
                            //'hideCondition' => !$this->getTelegramUser()->location_lat || !$this->getTelegramUser()->location_lon,
                            'text' => Yii::t('bot', 'MY LOCATION'),
                            'callback' => function (AdSearch $model) {
                                $latitude = 0;//$this->getTelegramUser()->location_lat;
                                $longitude = 0;//$this->getTelegramUser()->location_lon;
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
     * @return array
     */
    public function actionIndex($adSection = null, $page = 1)
    {
        $this->getState()->setName(null);

        $globalUser = $this->getUser();

        $query = AdSearch::find()
            ->where([
                'section' => $adSection,
            ])
            ->userOwner()
            ->orderBy([
                'status' => SORT_DESC,
                'title' => SORT_ASC,
            ]);

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $buttons = [];

        $searches = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($searches) {
            foreach ($searches as $search) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('view', [
                        'id' => $search->id,
                    ]),
                    'text' => ($search->isActive() ? '' : Emoji::INACTIVE . ' ') . '#' . $search->id . ' ' . $search->title,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($adSection) {
                return self::createRoute('index', [
                    'adSection' => $adSection,
                    'page' => $page,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $rowButtons[] = [
            'callback_data' => AdController::createRoute(),
            'text' => Emoji::BACK,
        ];

        $rowButtons[] = [
            'callback_data' => MenuController::createRoute(),
            'text' => Emoji::MENU,
        ];

        $matchesCount = AdSearchMatch::find()
            ->joinWith('adSearch')
            ->andWhere([
                AdSearch::tableName() . '.user_id' => $globalUser->id,
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
     * @param int $id AdSearch->id
     * @return array
     */
    public function actionView($id = null)
    {
        $globalUser = $this->getUser();

        $search = $globalUser->getAdSearches()
            ->where([
                'id' => $id,
            ])
            ->one();

        if (!isset($search)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $buttons[] = [
            [
                'callback_data' => self::createRoute('set-status', [
                    'id' => $search->id,
                ]),
                'text' => $search->isActive() ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
            ]
        ];

        $matchesCount = $search->getMatches()->count();

        if ($matchesCount) {
            $buttons[][] = [
                'callback_data' => self::createRoute('matches', [
                    'id' => $search->id,
                ]),
                'text' => Emoji::OFFERS . ' ' . $matchesCount,
            ];
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('index', [
                    'adSection' => $search->section,
                ]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'callback_data' => self::createRoute('update', [
                    'id' => $search->id,
                ]),
                'text' => Emoji::EDIT,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('view', [
                    'model' => $search,
                    'keywords' => self::getKeywordsAsString($search->getKeywords()->all()),
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @param int $id AdSearch->id
     * @param int $page
     * @return array
     */
    public function actionMatches($id = null, $page = 1)
    {
        $globalUser = $this->getUser();

        $search = $globalUser->getAdSearches()
            ->where([
                'id' => $id,
            ])
            ->one();

        if (!isset($search)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $query = $search->getMatchesOrderByRank();
        $matchesCount = $query->count();

        if (!$matchesCount) {
            return $this->actionView($search->id);
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

        $offer = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->one();

        if (!$offer) {
            return $this->actionView($search->id);
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'id' => $search->id,
                ]),
                'text' => '#' . $search->id . ' ' . $search->title,
            ]
        ];

        $buttons[] = PaginationButtons::build($pagination, function ($page) use ($search) {
            return self::createRoute('matches', [
                'id' => $search->id,
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'id' => $search->id,
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
                $offer->getPhotos()->count() ? $offer->getPhotos()->one()->file_id : null,
                $this->render('match', [
                    'model' => $offer,
                    'keywords' => self::getKeywordsAsString($offer->getKeywords()->all()),
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @param int $adSection
     * @param int $page
     * @return array
     */
    public function actionSectionMatches($adSection = null, $page = 1)
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
     * @param int $id AdSearch->id
     * @return array
     */
    public function actionSetStatus($id = null)
    {
        $model = AdSearch::find()
            ->where([
                'id' => $id,
            ])
            ->userOwner()
            ->one();

        if (!isset($model)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        switch ($model->status) {
            case AdSearch::STATUS_ON:
                $model->setInactive();
                $model->save(false);

                break;
            case AdSearch::STATUS_OFF:
                $scenario = new SetActiveScenario($model);

                if ($scenario->run()) {
                    $model->save(false);
                } else {
                    return $this->getResponseBuilder()
                        ->answerCallbackQuery(
                            $this->render('../alert', [
                                'alert' => $scenario->getFirstError(),
                            ]),
                            true
                        )
                        ->build();
                }
        }

        return $this->actionView($model->id);
    }

    /**
     * @param int $id AdSearch->id
     * @return array
     */
    public function actionDelete($id = null)
    {
        $model = AdSearch::find()
            ->where([
                'id' => $id,
            ])
            ->userOwner()
            ->one();

        if (!isset($model)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $adSection = $model->section;

        $model->unlinkAll('keywords', true);
        $model->delete();

        return $this->actionIndex($adSection);
    }
}
