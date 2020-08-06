<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\crud\CrudController;
use app\behaviors\SetAttributeValueBehavior;
use app\behaviors\SetDefaultCurrencyBehavior;
use app\models\Currency;
use app\models\Resume;
use app\models\User;
use app\models\JobKeyword;
use app\models\JobResumeKeyword;
use app\modules\bot\components\crud\rules\ExplodeStringFieldComponent;
use app\modules\bot\components\crud\rules\LocationToArrayFieldComponent;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\helpers\ListButtons;
use app\modules\bot\models\User as TelegramUser;
use app\modules\bot\components\helpers\Emoji;
use yii\data\Pagination;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;

/**
 * Class ResumeController
 *
 * @package app\modules\bot\controllers\privates
 */
class ResumeController extends CrudController
{
    /** @inheritDoc */
    protected function rules()
    {
        return [
            [
                'model' => Resume::class,
                'prepareViewParams' => function ($params) {
                    /** @var Resume $model */
                    $model = $params['model'] ?? null;

                    return [
                        'model' => $model,
                        'name' => $model->name,
                        'hourlyRate' => $model->min_hourly_rate,
                        'experiences' => $model->experiences,
                        'expectations' => $model->expectations,
                        'skills' => $model->skills,
                        'currencyCode' => $model->currencyCode,
                        'keywords' => self::getKeywordsAsString($model->getKeywordsRelation()->all()),
                        'isActive' => $model->isActive(),
                        'remote_on' => $model->remote_on,
                        'locationLink' => ExternalLink::getOSMLink($model->location_lat, $model->location_lon),
                    ];
                },
                'view' => 'show',
                'attributes' => [
                    'name' => [],
                    'skills' => [
                        'isRequired' => false,
                    ],
                    'experiences' => [
                        'isRequired' => false,
                    ],
                    'expectations' => [
                        'isRequired' => false,
                    ],
                    'keywords' => [
                        //'enableAddButton' = true,
                        'isRequired' => false,
                        'relation' => [
                            'model' => JobResumeKeyword::class,
                            'attributes' => [
                                'resume_id' => [Resume::class, 'id'],
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
                    'min_hourly_rate' => [
                        'isRequired' => false,
                        'buttons' => [
                            [
                                'text' => Yii::t('bot', 'Edit currency'),
                                'item' => 'currency',
                            ],
                        ],
                        'prepareViewParams' => function ($params) {
                            /** @var Resume $model */
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
                                'callback' => function (Resume $model) {
                                    $model->remote_on = Resume::REMOTE_ON;

                                    return $model;
                                },
                            ],
                            [
                                'text' => Yii::t('bot', 'No'),
                                'callback' => function (Resume $model) {
                                    $model->remote_on = Resume::REMOTE_OFF;

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
                                'callback' => function (Resume $model) {
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
                    'search_radius' => [
                        'buttons' => [
                            [
                                'text' => Yii::t('bot', 'NO'),
                                'callback' => function (Resume $model) {
                                    $model->search_radius = 0;

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
     * @param int $page
     *
     * @return array
     */
    public function actionIndex($page = 1)
    {
        $this->getState()->setName(null);
        $user = $this->getUser();

        $resumesCount = $user->getResumes()->count();

        $pagination = new Pagination([
            'totalCount' => $resumesCount,
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $resumes = $user->getResumes()
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $buttons = array_map(function ($resume) {
            return [
                [
                    'text' => ($resume->isActive() ? '' : Emoji::INACTIVE . ' ') . $resume->name,
                    'callback_data' => self::createRoute('view', [
                        'resumeId' => $resume->id,
                    ]),
                ],
            ];
        }, $resumes);

        $buttons[] = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('index', [
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'text' => Emoji::BACK,
                'callback_data' => SJobController::createRoute(),
            ],
            [
                'text' => Emoji::MENU,
                'callback_data' => MenuController::createRoute(),
            ],
            [
                'text' => Emoji::ADD,
                'callback_data' => ResumeController::createRoute(
                    'create',
                    [
                        'm' => $this->getModelName(Resume::class),
                    ]
                ),
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                $buttons
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

    /** @inheritDoc */
    public function actionView($resumeId)
    {
        $user = $this->getUser();

        $resume = $user->getResumes()
            ->where([
                'id' => $resumeId,
            ])
            ->one();

        if (!isset($resume)) {
            return [];
        }

        $buttons[] = [
            [
                'text' => Yii::t('bot', 'Status') . ': ' . ($resume->isActive() ? 'ON' : 'OFF'),
                'callback_data' => self::createRoute('update-status', [
                    'resumeId' => $resumeId,
                    'isEnabled' => !$resume->isActive(),
                ]),
            ],
        ];

        $matchesCount = $resume->getMatches()->count();

        if ($matchesCount) {
            $buttons[][] = [
                'callback_data' => self::createRoute('matches', [
                    'resumeId' => $resumeId,
                ]),
                'text' => Emoji::OFFERS . ' ' . $matchesCount,
            ];
        }

        $buttons[] = [
            [
                'text' => Emoji::BACK,
                'callback_data' => self::createRoute('index'),
            ],
            [
                'text' => Emoji::MENU,
                'callback_data' => MenuController::createRoute(),
            ],
            [
                'text' => Emoji::EDIT,
                'callback_data' => self::createRoute('u', [
                    'm' => $this->getModelName(Resume::class),
                    'i' => $resumeId,
                ]),
            ],
            [
                'text' => Emoji::DELETE,
                'callback_data' => self::createRoute('delete', [
                    'resumeId' => $resumeId,
                ]),
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('show', [
                    'model' => $resume,
                    'name' => $resume->name,
                    'hourlyRate' => $resume->min_hourly_rate,
                    'experiences' => $resume->experiences,
                    'expectations' => $resume->expectations,
                    'skills' => $resume->skills,
                    'currencyCode' => $resume->currencyCode,
                    'keywords' => self::getKeywordsAsString($resume->getKeywordsRelation()->all()),
                    'isActive' => $resume->isActive(),
                    'remote_on' => $resume->remote_on,
                    'locationLink' => ExternalLink::getOSMLink($resume->location_lat, $resume->location_lon),
                ]),
                $buttons,
                true
            )
            ->build();
    }

    public function actionMatches($resumeId, $page = 1)
    {
        $resume = Resume::findOne($resumeId);
        $matchesQuery = $resume->getMatches();
        $matchesCount = $matchesQuery->count();

        if (!$matchesCount) {
            return $this->actionView($resumeId);
        }

        $pagination = new Pagination([
            'totalCount' => $matchesQuery->count(),
            'pageSize' => 1,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $buttons[] = [
            [
                'text' => $resume->name,
                'callback_data' => self::createRoute('view', [
                    'resumeId' => $resumeId,
                ]),
            ]
        ];

        $buttons[] = PaginationButtons::build($pagination, function ($page) use ($resumeId) {
            return self::createRoute('matches', [
                'resumeId' => $resumeId,
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'callback_data' => self::createRoute('view', [
                    'resumeId' => $resumeId,
                ]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        $vacancy = $matchesQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->one();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('match', [
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
                    'user' => TelegramUser::findOne(['user_id' => $vacancy->user_id]),
                ]),
                $buttons,
                true
            )
            ->build();
    }

    /**
     * @param $resumeId
     *
     * @return array
     */
    public function actionDelete($resumeId)
    {
        $user = $this->getUser();

        $resume = $user->getResumes()
            ->where([
                'id' => $resumeId,
            ])
            ->one();

        if (!isset($resume)) {
            return [];
        }

        $resume->delete();

        return $this->actionIndex();
    }

    /**
     * @param $resumeId
     * @param bool $isEnabled
     *
     * @return array
     */
    public function actionUpdateStatus($resumeId, $isEnabled = false)
    {
        $resume = Resume::findOne($resumeId);

        if (!isset($resume)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }
        $this->backRoute->make('view', compact('resumeId'));
        $this->endRoute->make('view', compact('resumeId'));

        if ($isEnabled && ($notFilledFields = $resume->notPossibleToChangeStatus())) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery(
                    $this->render('status-error', compact('notFilledFields')),
                    true
                )
                ->build();
        }

        $resume->setAttribute('status', (int)$isEnabled);
        $resume->save();

        return $this->actionView($resumeId);
    }

    /**
     * @param integer $id
     *
     * @return Resume|ActiveRecord
     */
    protected function getModel($id)
    {
        return ($id == null) ? new Resume() : Resume::findOne(['id' => $id, 'user_id' => $this->getUser()->id]);
    }
}
