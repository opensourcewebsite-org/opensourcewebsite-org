<?php
namespace app\modules\bot\controllers\privates;

use app\models\Currency;
use app\modules\bot\components\helpers\PaginationButtons;
use Yii;
use app\models\Vacancy;
use app\models\Company;
use app\modules\bot\components\FillablePropertiesController;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;
use yii\data\Pagination;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;

class VacanciesController extends FillablePropertiesController
{
    protected static $properties = [
            'name',
            'employment',
            'hours_of_employment',
            'salary',
            'requirements',
            'skills_description',
            'conditions',
            'responsibilities',
        ];

    public function actionIndex($companyId, $page = 1)
    {
        $company = Company::findOne($companyId);
        if (!isset($company)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
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
                    'callback_data' => self::createRoute('show', [
                        'vacancyId' => $vacancy->id,
                    ]),
                ]
            ];
        }, $vacancies);
        $rows = array_merge($rows, [ $paginationButtons ]);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'companyName' => $company->name,
                    'vacanciesCount' => $vacanciesCount,
                ]),
                array_merge($rows, [
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => CompaniesController::createRoute('show', [
                                'companyId' => $companyId,
                            ]),
                        ],
                        [
                            'text' => Emoji::MENU,
                            'callback_data' => MenuController::createRoute(),
                        ],
                        [
                            'text' => Emoji::ADD,
                            'callback_data' => VacanciesController::createRoute('create', [
                                'companyId' => $companyId,
                            ]),
                        ]
                    ]
                ])
            )
            ->build();
    }

    public function actionCreate($companyId)
    {
        $this->getState()->setIntermediateField('companyId', $companyId);
        return $this->actionSetProperty(reset(static::$properties));
    }

    public function actionShow($vacancyId)
    {
        $vacancy = Vacancy::findOne($vacancyId);
        if (!isset($vacancy)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        $isEnabled = $vacancy->status == 1;
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('show', [
                    'name' => $vacancy->name,
                    'employment' => $vacancy->employment,
                    'hoursOfEmployment' => $vacancy->hours_of_employment,
                    'salary' => $vacancy->salary,
                    'requirements' => $vacancy->requirements,
                    'skillsDescription' => $vacancy->skills_description,
                    'conditions' => $vacancy->conditions,
                    'responsibilities' => $vacancy->responsibilities,
                    'currency' => $vacancy->currency,
                ]),
                [
                    [
                        [
                            'text' => Yii::t('bot', 'Status') . ': ' . Yii::t('bot', $isEnabled ? 'ON' : 'OFF'),
                            'callback_data' => self::createRoute('update-status', [
                                'vacancyId' => $vacancyId,
                                'isEnabled' => !$isEnabled,
                                'test' => 0,
                            ]),
                        ],
                    ],
                    [
                        [
                            'text' => '🙋‍♂️ 3',
                            'callback_data' => self::createRoute('show', [
                                'vacancyId' => $vacancyId,
                            ]),
                        ],
                    ],
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute('index', [
                                'companyId' => $vacancy->company->id
                            ]),
                        ],
                        [
                            'text' => Emoji::MENU,
                            'callback_data' => MenuController::createRoute(),
                        ],
                        [
                            'text' => Emoji::EDIT,
                            'callback_data' => self::createRoute('update', [
                                'vacancyId' => $vacancyId
                            ]),
                        ],
                        [
                            'text' => Emoji::DELETE,
                            'callback_data' => self::createRoute('delete', [
                                'vacancyId' => $vacancyId
                            ]),
                        ],
                    ],
                ],
                true
            )
            ->build();
    }

    public function actionUpdate($vacancyId)
    {
        $vacancyExists = Vacancy::find()->where([ 'id' => $vacancyId ])->exists();
        if (!$vacancyExists) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageReplyMarkup([
                [
                    [
                        'text' => Yii::t('bot', 'Name'),
                        'callback_data' => self::createRoute('set-property', [
                            'id' => $vacancyId,
                            'property' => 'name',
                        ]),
                    ],
                ],
                [
                    [
                        'text' => Yii::t('bot', 'Employment'),
                        'callback_data' => self::createRoute('set-property', [
                            'id' => $vacancyId,
                            'property' => 'employment',
                        ]),
                    ],
                ],
                [
                    [
                        'text' => Yii::t('bot', 'Hours of employment'),
                        'callback_data' => self::createRoute('set-property', [
                            'id' => $vacancyId,
                            'property' => 'hours-of-employment',
                        ]),
                    ],
                ],
                [
                    [
                        'text' => Yii::t('bot', 'Salary'),
                        'callback_data' => self::createRoute('set-property', [
                            'id' => $vacancyId,
                            'property' => 'salary',
                        ]),
                    ],
                ],
                [
                    [
                        'text' => Yii::t('bot', 'Requirements'),
                        'callback_data' => self::createRoute('set-property', [
                            'id' => $vacancyId,
                            'property' => 'requirements',
                        ]),
                    ],
                ],
                [
                    [
                        'text' => Yii::t('bot', 'Skills description'),
                        'callback_data' => self::createRoute('set-property', [
                            'id' => $vacancyId,
                            'property' => 'skills-description',
                        ]),
                    ],
                ],
                [
                    [
                        'text' => Yii::t('bot', 'Conditions'),
                        'callback_data' => self::createRoute('set-property', [
                            'id' => $vacancyId,
                            'property' => 'conditions',
                        ]),
                    ],
                ],
                [
                    [
                        'text' => Yii::t('bot', 'Responsibilities'),
                        'callback_data' => self::createRoute('set-property', [
                            'id' => $vacancyId,
                            'property' => 'responsibilities',
                        ]),
                    ],
                ],
                [
                    [
                        'text' => Emoji::BACK,
                        'callback_data' => self::createRoute('show', [
                            'id' => $vacancyId,
                        ]),
                    ],
                ],
            ])
            ->build();
    }

    /**
     * @param $vacancyId
     * @return array
     */
    public function actionDelete($vacancyId)
    {
        $vacancy = Vacancy::findOne($vacancyId);
        if (!isset($vacancy)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
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
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        $vacancy->setAttribute('status', (int)$isEnabled);
        $vacancy->save();

        return $this->actionShow($vacancyId);
    }

    /**
     * @param ActiveRecord $model
     * @param bool $isNew
     * @return array
     */
    protected function afterSave(ActiveRecord $model, bool $isNew)
    {
        return $this->actionShow($model->id);
    }

    protected function getModel($id)
    {
        return !is_null($id)
            ? Vacancy::findOne($id)
            : new Vacancy([
                'company_id' => $this->getState()->getIntermediateField('companyId', null),
                'currency_id' => Currency::findOne([ 'code' => 'USD' ])->id,
            ]);
    }
}
