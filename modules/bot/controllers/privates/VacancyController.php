<?php

namespace app\modules\bot\controllers\privates;

use app\behaviors\SetAttributeValueBehavior;
use app\models\Company;
use app\models\Currency;
use app\modules\bot\components\CrudController;
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
                        'name' => $model->name,
                        'hourlyRate' => $this->getDisplayHourlyRate($model),
                        'requirements' => $model->requirements,
                        'conditions' => $model->conditions,
                        'responsibilities' => $model->responsibilities,
                        'currency' => $model->currency,
                        'company' => $model->company,
                        'isActive' => $model->isActive(),
                    ];
                },
                'view' => 'show',
                'attributes' => [
                    'name' => [],
                    'currency' => [
                        'relation' => [
                            'attributes' => [
                                'currency_id' => [Currency::class, 'id', 'code'],
                            ],
                        ],
                    ],
                    'max_hourly_rate' => [
                        'isRequired' => false,
                    ],
                    'requirements' => [],
                    'conditions' => [],
                    'responsibilities' => [],
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
    public function actionIndex($companyId, $page = 1)
    {
        $this->getState()->setIntermediateField(self::SAFE_ATTRIBUTE, $companyId);
        $company = Company::findOne($companyId);
        if (!isset($company)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $vacanciesCount = $company->getVacancies()->count();
        $pagination = new Pagination([
            'totalCount' => $vacanciesCount,
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);
        $vacancies = $company->getVacancies()
            ->offset($pagination->offset)
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
                    'text' => $vacancy->name,
                    'callback_data' => self::createRoute('view', [
                        'vacancyId' => $vacancy->id,
                    ]),
                ],
            ];
        }, $vacancies);
        $rows = array_merge($rows, [$paginationButtons]);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'companyName' => $company->name,
                    'vacanciesCount' => $vacanciesCount,
                ]),
                array_merge($rows, [
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => CompanyController::createRoute('view', [
                                'companyId' => $companyId,
                            ]),
                        ],
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

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('show', [
                    'name' => $vacancy->name,
                    'hourlyRate' => $this->getDisplayHourlyRate($vacancy),
                    'requirements' => $vacancy->requirements,
                    'conditions' => $vacancy->conditions,
                    'responsibilities' => $vacancy->responsibilities,
                    'currency' => $vacancy->currency,
                    'company' => $vacancy->company,
                    'isActive' => $vacancy->isActive(),
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
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute('index', [
                                'companyId' => $vacancy->company->id,
                            ]),
                        ],
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

    /**
     * @param Vacancy $vacancy
     *
     * @return string|null
     */
    private function getDisplayHourlyRate(Vacancy $vacancy)
    {
        if (isset($vacancy->max_hour_rate)) {
            return Yii::t('bot', 'till') . " {$vacancy->max_hour_rate}";
        }

        return null;
    }
}
