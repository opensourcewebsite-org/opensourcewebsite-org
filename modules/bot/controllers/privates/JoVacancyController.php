<?php

namespace app\modules\bot\controllers\privates;

use app\behaviors\SetAttributeValueBehavior;
use app\behaviors\SetDefaultCurrencyBehavior;
use app\models\Company;
use app\models\Currency;
use app\models\events\interfaces\ViewedByUserInterface;
use app\models\JobKeyword;
use app\models\JobVacancyKeyword;
use app\models\JobVacancyMatch;
use app\models\Language;
use app\models\LanguageLevel;
use app\models\scenarios\Vacancy\SetActiveScenario;
use app\models\User as GlobalUser;
use app\models\Vacancy;
use app\models\VacancyLanguage;
use app\modules\bot\components\crud\CrudController;
use app\modules\bot\components\crud\rules\ExplodeStringFieldComponent;
use app\modules\bot\components\crud\rules\LocationToArrayFieldComponent;
use app\modules\bot\components\crud\services\IntermediateFieldService;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\User;
use Yii;
use yii\data\Pagination;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;

/**
 * Class JoVacancyController
 *
 * @link https://opensourcewebsite.org/vacancy
 * @package app\modules\bot\controllers\privates
 */
class JoVacancyController extends CrudController
{
    protected $updateAttributes = [
        'name',
        'responsibilities',
        'requirements',
        'conditions',
        'keywords',
        'languages',
        'max_hourly_rate',
        'remote_on',
        'location',
    ];

    /**
     * {@inheritdoc}
     */
    protected function rules()
    {
        return [
            'model' => Vacancy::class,
            'prepareViewParams' => function ($params) {
                /** @var Vacancy $model */
                $model = $params['model'] ?? null;

                return [
                    'model' => $model,
                    'company' => $model->company,
                ];
            },
            'attributes' => [
                'name' => [],
                'responsibilities' => [],
                'requirements' => [],
                'conditions' => [],
                'keywords' => [
                    //'enableAddButton' = true,
                    'isRequired' => false,
                    'relation' => [
                        'model' => JobVacancyKeyword::class,
                        'attributes' => [
                            'vacancy_id' => [Vacancy::class, 'id'],
                            'job_keyword_id' => [JobKeyword::class, 'id', 'keyword'],
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
                'languages' => [
                    'viewAfterAdd' => 'set-languages',
                    'enableAddButton' => true,
                    'showRowsList' => true,
                    'createRelationIfEmpty' => true,
                    'relation' => [
                        'model' => VacancyLanguage::class,
                        'attributes' => [
                            'vacancy_id' => [Vacancy::class, 'id'],
                            'language_id' => [Language::class, 'id', 'code'],
                            'language_level_id' => [LanguageLevel::class, 'id', 'code'],
                        ],
                        'removeOldRows' => true,
                    ],
                    'buttons' => [
                        [
                            [
                                'editMode' => false,
                                'text' => Yii::t('bot', 'NEXT'),
                                'callback' => function (Vacancy $model) {
                                    return $model;
                                },
                            ],
                        ]
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
                'max_hourly_rate' => [
                    'isRequired' => false,
                    'buttons' => [
                        [
                            [
                                'text' => Yii::t('bot', 'Edit currency'),
                                'item' => 'currency',
                            ],
                        ]
                    ],
                ],
                'remote_on' => [
                    'buttons' => [
                        [
                            [
                                'text' => Yii::t('bot', 'YES'),
                                'callback' => function (Vacancy $model) {
                                    $model->remote_on = Vacancy::REMOTE_ON;

                                    return $model;
                                },
                            ],
                            [
                                'text' => Yii::t('bot', 'NO'),
                                'callback' => function (Vacancy $model) {
                                    $model->remote_on = Vacancy::REMOTE_OFF;

                                    return $model;
                                },
                            ],
                        ]
                    ],
                ],
                'location' => [
                    'isRequired' => false,
                    'component' => LocationToArrayFieldComponent::class,
                    'buttons' => [
                        [
                            [
                                'hideCondition' => !isset($this->getTelegramUser()->userLocation),
                                'text' => Yii::t('bot', 'MY LOCATION'),
                                'callback' => function (Vacancy $model) {
                                    $latitude = $this->getTelegramUser()->userLocation->location_lat;
                                    $longitude = $this->getTelegramUser()->userLocation->location_lon;
                                    if ($latitude && $longitude) {
                                        $model->location_lat = $latitude;
                                        $model->location_lon = $longitude;

                                        return $model;
                                    }

                                    return null;
                                },
                            ],
                        ]
                    ],
                ],
                'company_id' => [
                    'behaviors' => [
                        'SetAttributeValueBehavior' => [
                            'class' => SetAttributeValueBehavior::class,
                            'attributes' => [
                                ActiveRecord::EVENT_BEFORE_VALIDATE => ['company_id'],
                                ActiveRecord::EVENT_BEFORE_INSERT => ['company_id'],
                            ],
                            'attribute' => 'company_id',
                            'value' => $this->getState()
                                ->getIntermediateField(IntermediateFieldService::SAFE_ATTRIBUTE),
                        ],
                    ],
                    'hidden' => true,
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
     * @param int|null $companyId Company->id
     * @param int $page
     * @return array
     */
    public function actionIndex($companyId = null, $page = 1)
    {
        $this->getState()->setName(null);
        $this->state->setIntermediateField(IntermediateFieldService::SAFE_ATTRIBUTE, $companyId);

        $globalUser = $this->getUser();

        if ($companyId) {
            $company = Company::findOne($companyId);

            if (!isset($company)) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery()
                    ->build();
            }

            $query = $company->getVacancies()
                ->orderBy([
                    'status' => SORT_DESC,
                    'name' => SORT_ASC,
                ]);
        } else {
            $company = null;

            $query = $globalUser->getVacancies()
                ->andWhere([
                    'IS', 'company_id', null,
                ])
                ->orderBy([
                    'status' => SORT_DESC,
                    'name' => SORT_ASC,
                ]);
        }

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

        $vacancies = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($vacancies) {
            foreach ($vacancies as $vacancy) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('view', [
                        'id' => $vacancy->id,
                    ]),
                    'text' => ($vacancy->isActive() ? '' : Emoji::INACTIVE . ' ') . '#' . $vacancy->id . ' ' . $vacancy->name,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($companyId) {
                return self::createRoute('index', [
                    'companyId' => $companyId,
                    'page' => $page,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        if ($company) {
            $rowButtons[] = [
                'callback_data' => JoCompanyController::createRoute('view', [
                    'id' => $companyId,
                ]),
                'text' => Emoji::BACK,
            ];
        } else {
            $rowButtons[] = [
                'callback_data' => JoController::createRoute(),
                'text' => Emoji::BACK,
            ];
        }

        $rowButtons[] = [
            'callback_data' => MenuController::createRoute(),
            'text' => Emoji::MENU,
        ];

        $matchesCount = $globalUser->getVacancyMatches()->count();

        if ($matchesCount) {
            $rowButtons[] = [
                'callback_data' => self::createRoute('all-matches'),
                'text' => Emoji::OFFERS . ' ' . $matchesCount,
            ];
        }

        $newMatchesCount = $globalUser->getVacancyNewMatches()->count();

        if ($newMatchesCount) {
            $rowButtons[] = [
                'callback_data' => self::createRoute('all-new-matches'),
                'text' => Emoji::OFFERS . ' ' . Emoji::NEW1,
            ];
        }

        $rowButtons[] = [
            'callback_data' => self::createRoute('create'),
            'text' => Emoji::ADD,
        ];

        $buttons[] = $rowButtons;

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'companyName' => $company ? $company->name : null,
                ]),
                $buttons
            )
            ->build();
    }

    /**
     * @param int $id Vacancy->id
     * @return array
     */
    public function actionView($id = null)
    {
        $vacancy = Vacancy::find()
            ->where([
                'id' => $id,
            ])
            ->userOwner()
            ->one();

        if (!isset($vacancy)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $buttons[] = [
            [
                'callback_data' => self::createRoute('set-status', [
                    'id' => $vacancy->id,
                ]),
                'text' => $vacancy->isActive() ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
            ],
        ];

        if ($company = $vacancy->company) {
            $rowButtons[] = [
                'callback_data' => self::createRoute('index', [
                    'companyId' => $company->id,
                ]),
                'text' => Emoji::BACK,
            ];
        } else {
            $rowButtons[] = [
                'callback_data' => self::createRoute(),
                'text' => Emoji::BACK,
            ];
        }

        $rowButtons[] = [
            'callback_data' => MenuController::createRoute(),
            'text' => Emoji::MENU,
        ];

        $matchesCount = $vacancy->getMatches()->count();

        if ($matchesCount) {
            $rowButtons[] = [
                'callback_data' => self::createRoute('matches', [
                    'id' => $vacancy->id,
                ]),
                'text' => Emoji::OFFERS . ' ' . $matchesCount,
            ];
        }

        $newMatchesCount = $vacancy->getNewMatches()->count();

        if ($newMatchesCount) {
            $rowButtons[] = [
                'callback_data' => self::createRoute('new-matches', [
                    'id' => $vacancy->id,
                ]),
                'text' => Emoji::OFFERS . ' ' . Emoji::NEW1,
            ];
        }

        $rowButtons[] = [
            'callback_data' => self::createRoute('update', [
                'id' => $vacancy->id,
            ]),
            'text' => Emoji::EDIT,
        ];

        $buttons[] = $rowButtons;

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('view', [
                    'model' => $vacancy,
                    'company' => $vacancy->company,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @param int $id Vacancy->id
     * @param int $page
     * @return array
     */
    public function actionMatches($id = null, $page = 1)
    {
        $vacancy = Vacancy::find()
            ->where([
                'id' => $id,
            ])
            ->userOwner()
            ->one();

        if (!isset($vacancy)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $query = $vacancy->getMatches()
            ->orderByRank();

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 1,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $vacancyMatch = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->one();

        if (!$vacancyMatch) {
            return $this->actionView($vacancy->id);
        }

        $this->getState()->setName(null);

        $resume = $vacancyMatch->resume;

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'id' => $vacancy->id,
                ]),
                'text' => '#' . $vacancy->id . ' ' . $vacancy->name,
            ]
        ];

        $buttons[] = PaginationButtons::build($pagination, function ($page) use ($vacancy) {
            return self::createRoute('matches', [
                'id' => $vacancy->id,
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'id' => $vacancy->id,
                ]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        $isNewMatch = false;

        if ($vacancyMatch->isNew()) {
            $isNewMatch = true;

            $resume->markViewedByUserId($this->globalUser->id);
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('match', [
                    'model' => $resume,
                    'isNewMatch' => $isNewMatch,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @param int $id Vacancy->id
     * @return array
     */
    public function actionNewMatches($id = null)
    {
        $vacancy = Vacancy::find()
            ->where([
                'id' => $id,
            ])
            ->userOwner()
            ->one();

        if (!isset($vacancy)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $vacancyMatch = $vacancy->getNewMatches()
            ->orderByRank()
            ->one();

        if (!$vacancyMatch) {
            return $this->actionMatches($vacancy->id);
        }

        $this->getState()->setName(null);

        $resume = $vacancyMatch->resume;

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'id' => $vacancy->id,
                ]),
                'text' => '#' . $vacancy->id . ' ' . $vacancy->name,
            ]
        ];

        $buttons[] = [
            [
                'callback_data' => self::createRoute('new-matches', [
                    'id' => $vacancy->id,
                ]),
                'text' => '>',
            ],
        ];

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'id' => $vacancy->id,
                ]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        $isNewMatch = false;

        if ($vacancyMatch->isNew()) {
            $isNewMatch = true;

            $resume->markViewedByUserId($this->globalUser->id);
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('match', [
                    'model' => $resume,
                    'isNewMatch' => $isNewMatch,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @param int $page
     * @return array
     */
    public function actionAllMatches($page = 1)
    {
        $query = $this->globalUser->getVacancyMatches()
            ->orderByRank();

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 1,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $vacancyMatch = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->one();

        if (!$vacancyMatch) {
            return $this->actionIndex();
        }

        $this->getState()->setName(null);

        $vacancy = $vacancyMatch->vacancy;
        $resume = $vacancyMatch->resume;

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'id' => $vacancy->id,
                ]),
                'text' => '#' . $vacancy->id . ' ' . $vacancy->name,
            ]
        ];

        $buttons[] = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('all-matches', [
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'callback_data' => self::createRoute('index'),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        $isNewMatch = false;

        if ($vacancyMatch->isNew()) {
            $isNewMatch = true;

            $resume->markViewedByUserId($this->globalUser->id);
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('match', [
                    'model' => $resume,
                    'isNewMatch' => $isNewMatch,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionAllNewMatches()
    {
        $vacancyMatch = $this->globalUser->getVacancyNewMatches()
            ->orderByRank()
            ->one();

        if (!$vacancyMatch) {
            return $this->actionAllMatches();
        }

        $this->getState()->setName(null);

        $vacancy = $vacancyMatch->vacancy;
        $resume = $vacancyMatch->resume;

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'id' => $vacancy->id,
                ]),
                'text' => '#' . $vacancy->id . ' ' . $vacancy->name,
            ]
        ];

        $buttons[] = [
            [
                'callback_data' => self::createRoute('all-new-matches'),
                'text' => '>',
            ],
        ];

        $buttons[] = [
            [
                'callback_data' => self::createRoute('index'),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        $isNewMatch = false;

        if ($vacancyMatch->isNew()) {
            $isNewMatch = true;

            $resume->markViewedByUserId($this->globalUser->id);
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('match', [
                    'model' => $resume,
                    'isNewMatch' => $isNewMatch,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @param int $id Vacancy->id
     * @return array
     */
    public function actionSetStatus($id = null)
    {
        $model = Vacancy::find()
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
            case Vacancy::STATUS_ON:
                $model->setInactive();
                $model->save(false);

                break;
            case Vacancy::STATUS_OFF:
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
     * @param int $id Vacancy->id
     * @return array
     */
    public function actionDelete($id = null)
    {
        $user = $this->getUser();

        $vacancy = $user->getVacancies()
            ->where([
                'id' => $id,
            ])
            ->one();

        if (!isset($vacancy)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ($company = $vacancy->company) {
            $companyId = $vacancy->company->id;
        }

        $vacancy->delete();

        return $this->actionIndex($company ? $companyId : null);
    }

    /**
     * @param integer $id Vacancy->id
     * @return Vacancy|ActiveRecord
     */
    protected function getModel($id)
    {
        return !is_null($id)
            ? Vacancy::findOne($id)
            : new Vacancy([
                'company_id' => $this->getState()->getIntermediateField('companyId', null),
                'currency_id' => Currency::findOne(['code' => 'USD'])->id,
            ]);
    }
}
