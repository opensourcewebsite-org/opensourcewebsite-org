<?php

namespace app\modules\bot\controllers\privates;

use app\behaviors\SetAttributeValueBehavior;
use app\behaviors\SetDefaultCurrencyBehavior;
use app\models\Company;
use app\models\Currency;
use app\models\Language;
use app\models\LanguageLevel;
use app\models\User;
use app\models\VacancyLanguage;
use app\modules\bot\components\crud\CrudController;
use app\modules\bot\components\crud\rules\ExplodeStringFieldComponent;
use app\modules\bot\components\crud\rules\LocationToArrayFieldComponent;
use app\modules\bot\components\crud\services\IntermediateFieldService;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\models\JobKeyword;
use app\modules\bot\models\JobVacancyKeyword;
use app\modules\bot\models\User as TelegramUser;
use Yii;
use app\models\Vacancy;
use app\modules\bot\components\helpers\Emoji;

use yii\data\Pagination;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;

/**
 * Class VacanciesController
 *
 * @package app\modules\bot\controllers\privates
 */
class VacancyController extends CrudController
{
    protected static $properties = [
        'name',
        'min_hourly_rate',
        'max_hourly_rate',
        'requirements',
        'conditions',
        'responsibilities',
    ];

    /** @inheritDoc */
    protected function rules()
    {
        return [
            [
                'model' => Vacancy::class,
                'prepareViewParams' => function ($params) {
                    /** @var Vacancy $model */
                    $model = $params['model'] ?? null;

                    return [
                        'model' => $model,
                        'name' => $model->name,
                        'hourlyRate' => $model->max_hourly_rate,
                        'requirements' => $model->requirements,
                        'conditions' => $model->conditions,
                        'responsibilities' => $model->responsibilities,
                        'currencyCode' => $model->currencyCode,
                        'company' => $model->company,
                        'isActive' => $model->isActive(),
                        'remote_on' => $model->remote_on,
                        'keywords' => self::getKeywordsAsString($model->getKeywordsRelation()->all()),
                        'locationLink' => ExternalLink::getOSMLink($model->location_lat, $model->location_lon),
                        'languages' => array_map(function ($vacancyLanguage) {
                            return $vacancyLanguage->getDisplayName();
                        }, $model->vacancyLanguagesRelation),
                    ];
                },
                'view' => 'show',
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
                        'samePageAfterAdd' => true,
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
                                'editMode' => false,
                                'text' => Yii::t('bot', 'NEXT'),
                                'callback' => function (Vacancy $model) {
                                    return $model;
                                },
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
                    'max_hourly_rate' => [
                        'isRequired' => false,
                        'buttons' => [
                            [
                                'text' => Yii::t('bot', 'Edit currency'),
                                'item' => 'currency',
                            ],
                        ],
                        'prepareViewParams' => function ($params) {
                            /** @var Vacancy $model */
                            $model = $params['model'];

                            return array_merge($params, [
                                'currencyCode' => $model->currencyCode,
                            ]);
                        },
                    ],
                    'remote_on' => [
                        'buttons' => [
                            [
                                'text' => Yii::t('bot', 'Yes'),
                                'callback' => function (Vacancy $model) {
                                    $model->remote_on = Vacancy::REMOTE_ON;

                                    return $model;
                                },
                            ],
                            [
                                'text' => Yii::t('bot', 'No'),
                                'callback' => function (Vacancy $model) {
                                    $model->remote_on = Vacancy::REMOTE_OFF;

                                    return $model;
                                },
                            ],
                        ],
                    ],
                    'location' => [
                        'isRequired' => false,
                        'component' => LocationToArrayFieldComponent::class,
                        'buttons' => [
                            [
                                'hideCondition' => !$this->getTelegramUser()->location_lat || !$this->getTelegramUser()->location_lon,
                                'text' => Yii::t('bot', 'My location'),
                                'callback' => function (Vacancy $model) {
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

    /**
     * @param $companyId
     * @param int $page
     *
     * @return array
     */
    public function actionIndex($companyId = null, $page = 1)
    {
        $this->getState()->setIntermediateField(IntermediateFieldService::SAFE_ATTRIBUTE, $companyId);
        $company = Company::findOne($companyId);
        if ($companyId && !isset($company)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }
        $user = $this->getUser();

        if ($company) {
            $query = $company->getVacancies();
        } else {
            $query = $user->getVacancies()->andWhere(['IS', 'company_id', null]);
        }
        $vacanciesCount = $query->count();
        $pagination = new Pagination([
            'totalCount' => $vacanciesCount,
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);
        $vacancies = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($companyId) {
            return self::createRoute('index', [
                'companyId' => $companyId,
                'page' => $page,
            ]);
        });
        $rows = array_map(function ($vacancy) {
            return [
                [
                    'text' => ($vacancy->isActive() ? '' : '❌ ') . $vacancy->name,
                    'callback_data' => self::createRoute('view', [
                        'vacancyId' => $vacancy->id,
                    ]),
                ],
            ];
        }, $vacancies);
        $rows = array_merge($rows, [$paginationButtons]);
        if ($company) {
            $backButton = [
                'text' => Emoji::BACK,
                'callback_data' => CompanyController::createRoute('view', [
                    'companyId' => $companyId,
                ]),
            ];
        } else {
            $backButton = [
                'text' => Emoji::BACK,
                'callback_data' => SJobController::createRoute(),
            ];
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'companyName' => $company ? $company->name : null,
                    'vacanciesCount' => $vacanciesCount,
                ]),
                array_merge($rows, [
                    [
                        $backButton,
                        [
                            'text' => Emoji::MENU,
                            'callback_data' => MenuController::createRoute(),
                        ],
                        [
                            'text' => Emoji::ADD,
                            'callback_data' => VacancyController::createRoute(
                                'create',
                                [
                                    'm' => $this->getModelName(Vacancy::class),
                                ]
                            ),
                        ],
                    ],
                ])
            )
            ->build();
    }

    /** @inheritDoc */
    public function actionView($vacancyId)
    {
        $vacancy = Vacancy::findOne($vacancyId);
        if (!isset($vacancy)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $isEnabled = $vacancy->isActive();
        if ($company = $vacancy->company) {
            $backButton = [
                'text' => Emoji::BACK,
                'callback_data' => self::createRoute('index', [
                    'companyId' => $company->id,
                ]),
            ];
        } else {
            $backButton = [
                'text' => Emoji::BACK,
                'callback_data' => self::createRoute(),
            ];
        }

        $buttons = [];
        $buttons[] = [
            [
                'text' => Yii::t('bot', 'Status') . ': ' . Yii::t('bot', $isEnabled ? 'ON' : 'OFF'),
                'callback_data' => self::createRoute('update-status', [
                    'vacancyId' => $vacancyId,
                    'isEnabled' => !$isEnabled,
                ]),
            ],
        ];

        $matchedResumeCount = $vacancy->getMatches()->count();
        if ($matchedResumeCount > 0) {
            $buttons[][] = [
                'callback_data' => self::createRoute('resume-matches', ['vacancyId' => $vacancyId]),
                'text' => '🙋‍♂️ ' . $matchedResumeCount,
            ];
        }

        $buttons[] = [
            $backButton,
            [
                'text' => Emoji::MENU,
                'callback_data' => MenuController::createRoute(),
            ],
            [
                'text' => Emoji::EDIT,
                'callback_data' => self::createRoute(
                    'u',
                    [
                        'm' => $this->getModelName(Vacancy::class),
                        'i' => $vacancyId,
                    ]
                ),
            ],
            [
                'text' => Emoji::DELETE,
                'callback_data' => self::createRoute('delete', [
                    'vacancyId' => $vacancyId,
                ]),
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('show', [
                    'model' => $vacancy,
                    'name' => $vacancy->name,
                    'hourlyRate' => $vacancy->max_hourly_rate,
                    'requirements' => $vacancy->requirements,
                    'conditions' => $vacancy->conditions,
                    'responsibilities' => $vacancy->responsibilities,
                    'currencyCode' => $vacancy->currencyCode,
                    'company' => $vacancy->company,
                    'isActive' => $vacancy->isActive(),
                    'remote_on' => $vacancy->remote_on,
                    'keywords' => self::getKeywordsAsString($vacancy->getKeywordsRelation()->all()),
                    'locationLink' => ExternalLink::getOSMLink($vacancy->location_lat, $vacancy->location_lon),
                    'languages' => array_map(function ($vacancyLanguage) {
                        return $vacancyLanguage->getDisplayName();
                    }, $vacancy->vacancyLanguagesRelation),
                ]),
                $buttons,
                true
            )
            ->build();
    }

    /**
     * @param ActiveRecord[] $keywords
     *
     * @return string
     */
    private static function getKeywordsAsString($keywords)
    {
        $resultKeywords = [];

        foreach ($keywords as $keyword) {
            $resultKeywords[] = $keyword->keyword;
        }

        return implode(', ', $resultKeywords);
    }

    public function actionResumeMatches($vacancyId, $page = 1)
    {
        $resume = Vacancy::findOne($vacancyId);
        $vacanciesQuery = $resume->getMatches();

        $pagination = new Pagination(
            [
                'totalCount' => $vacanciesQuery->count(),
                'pageSize' => 1,
                'params' => [
                    'page' => $page,
                ],
                'pageSizeParam' => false,
                'validatePage' => true,
            ]
        );

        $paginationButtons = PaginationButtons::build(
            $pagination,
            function ($page) use ($vacancyId) {
                return self::createRoute(
                    'resume-matches',
                    [
                        'vacancyId' => $vacancyId,
                        'page' => $page,
                    ]
                );
            }
        );

        $buttons = [];

        $buttons[] = $paginationButtons;
        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', ['vacancyId' => $vacancyId]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        $resume = $vacanciesQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all()[0];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render(
                    'match',
                    [
                        'model' => $resume,
                        'name' => $resume->name,
                        'hourlyRate' => $resume->min_hourly_rate,
                        'experiences' => $resume->experiences,
                        'expectations' => $resume->expectations,
                        'skills' => $resume->skills,
                        'currencyCode' => $resume->currencyCode,
                        'isActive' => $resume->isActive(),
                        'remote_on' => $resume->remote_on,
                        'keywords' => self::getKeywordsAsString($resume->getKeywordsRelation()->all()),
                        'locationLink' => ExternalLink::getOSMLink($resume->location_lat, $resume->location_lon),
                        'user' => TelegramUser::findOne($resume->user_id),
                    ]
                ),
                $buttons,
                true
            )
            ->build();
    }

    /**
     * @param $vacancyId
     *
     * @return array
     */
    public function actionDelete($vacancyId)
    {
        $vacancy = Vacancy::findOne($vacancyId);
        if (!isset($vacancy)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $companyId = $vacancy->company->id;

        try {
            $vacancy->delete();
        } catch (StaleObjectException $e) {
        } catch (\Throwable $e) {
        }

        return $this->actionIndex($companyId);
    }

    public function actionUpdateStatus($vacancyId, $isEnabled = false)
    {
        $vacancy = Vacancy::findOne($vacancyId);
        if (!isset($vacancy)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }
        $this->backRoute->make('view', compact('vacancyId'));
        $this->endRoute->make('view', compact('vacancyId'));

        if ($isEnabled && ($notFilledFields = $vacancy->possibleToChangeStatus())) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery(
                    $this->render('status-error', compact('notFilledFields')),
                    true
                )
                ->build();
        }

        $vacancy->setAttribute('status', (int)$isEnabled);
        $vacancy->save();

        return $this->actionView($vacancyId);
    }

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
