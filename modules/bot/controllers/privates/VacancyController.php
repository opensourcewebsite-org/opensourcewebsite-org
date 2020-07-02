<?php

namespace app\modules\bot\controllers\privates;

use app\behaviors\SetAttributeValueBehavior;
use app\models\Company;
use app\models\Currency;
use app\modules\bot\components\crud\CrudController;
use app\modules\bot\components\crud\rules\LocationToArrayFieldComponent;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\components\helpers\PaginationButtons;
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
                        'locationLink' => ExternalLink::getOSMLink($model->location_lat, $model->location_lon),
                    ];
                },
                'view' => 'show',
                'attributes' => [
                    'name' => [],
                    'responsibilities' => [],
                    'requirements' => [],
                    'conditions' => [],
                    'currency' => [
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
                                'createMode' => false,
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
                                'value' => $this->getState()->getIntermediateField(CrudController::SAFE_ATTRIBUTE),
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
                                'value' => $this->getState()->getIntermediateField(CrudController::SAFE_ATTRIBUTE)
                                    ? null : $this->module->user->id,
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
        $this->getState()->setIntermediateField(self::SAFE_ATTRIBUTE, $companyId);
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
            $query = $user->getVacancies();
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
            return self::createRoute('show', [
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
                    'locationLink' => ExternalLink::getOSMLink($vacancy->location_lat, $vacancy->location_lon),
                ]),
                [
                    [
                        [
                            'text' => Yii::t('bot', 'Status') . ': ' . Yii::t('bot', $isEnabled ? 'ON' : 'OFF'),
                            'callback_data' => self::createRoute('update-status', [
                                'vacancyId' => $vacancyId,
                                'isEnabled' => !$isEnabled,
                            ]),
                        ],
                    ],
                    [
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
                    ],
                ],
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
