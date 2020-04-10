<?php
namespace app\modules\bot\controllers\privates;

use app\models\Company;
use app\models\Currency;
use app\models\Language;
use app\models\Vacancy;
use app\modules\bot\components\CrudController;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\response\ResponseBuilder;
use Yii;
use app\modules\bot\components\Controller;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;

class SJobController extends CrudController
{
    protected function rules()
    {
        return [
            [
                'model' => Company::class,
                'attributes' => [
                    'name' => [],
                    'description' => [
                        'isRequired' => false,
                    ],
                    'address' => [
                        'isRequired' => false,
                    ],
                    'url' => [
                        'isRequired' => false,
                    ],
                ],
            ],
            [
                'model' => Vacancy::class,
                'attributes' => [
                    'currency_id' => [
                        'relation' => [
                            'model' => Currency::class,
                            'foreign_key' => 'id',
                        ]
                    ],
                    'name' => [],
                    'hourly_rate' => [],
                    'requirements' => [],
                    'conditions' => [],
                    'responsibilities' => [],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                [
                    [
                        [
                            'text' => Yii::t('bot', 'Resumes'),
                            'callback_data' => ResumesController::createRoute(),
                        ],
                    ],
                    [
                        [
                            'text' => Yii::t('bot', 'Vacancies'),
                            'callback_data' => self::createRoute('company-index'),
                        ],
                    ],
                    [
                        [
                            'text' => Yii::t('bot', 'Companies'),
                            'callback_data' => self::createRoute('company-index'),
                        ],
                    ],
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => ServicesController::createRoute(),
                        ],
                    ],
                ]
            )
            ->build();
    }

    /* Company */

    public function actionCompanyIndex($page = 1)
    {
        $update = $this->getUpdate();

        $companyButtons = PaginationButtons::buildFromQuery(
            $this->getUser()->getCompanies(),
            function ($page) {
                return self::createRoute('company-index', [
                    'page' => $page,
                ]);
            },
            function (Company $company) {
                return [
                    'text' => $company->name,
                    'callback_data' => self::createRoute('show', [
                        'm' => $this->getModelName(Company::class),
                        'id' => $company->id,
                    ]),
                ];
            },
            $page
        );

        return ResponseBuilder::fromUpdate($update)
            ->editMessageTextOrSendMessage(
                $this->render('company/index'),
                array_merge($companyButtons, [
                    [
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
                            'callback_data' => self::createRoute('create', [
                                'm' => $this->getModelName(Company::class),
                            ]),
                        ],
                    ],
                ])
            )
            ->build();
    }

    public function actionCompanyDelete($companyId)
    {
        $company = Company::findOne($companyId);
        if (!isset($company)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        if (!empty($company->vacancies)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery(
                    new MessageText(Yii::t('bot', 'This company has vacancies. Delete them first and try again.')),
                    true
                )
                ->build();
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->getUser()->unlink('companies', $company, true);
            $company->delete();
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
        }

        return $this->actionCompanyIndex();
    }

    /* Vacancy */

    public function actionVacancyIndex($companyId, $page = 1)
    {
        $company = Company::findOne($companyId);
        if (!isset($company)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        $vacancies = PaginationButtons::buildFromQuery(
            $company->getVacancies(),
            function (int $page) use ($companyId) {
                return self::createRoute('vacancy-index', [
                    'companyId' => $companyId,
                    'page' => $page,
                ]);
            },
            function (Vacancy $vacancy) {
                return [
                    'text' => $vacancy->name,
                    'callback_data' => self::createRoute('show', [
                        'id' => $vacancy->id,
                        'm' => $this->getModelName(Vacancy::class),
                    ]),
                ];
            },
            $page
        );

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('vacancy/index', [
                    'companyName' => $company->name,
                    'vacanciesCount' => $company->getVacancies()->count(),
                ]),
                array_merge($vacancies, [
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute('show', [
                                'id' => $companyId,
                                'm' => $this->getModelName(Company::class),
                            ]),
                        ],
                        [
                            'text' => Emoji::MENU,
                            'callback_data' => MenuController::createRoute(),
                        ],
                        [
                            'text' => Emoji::ADD,
                            'callback_data' => self::createRoute('create', [
                                'm' => $this->getModelName(Vacancy::class),
                                'cid' => $companyId,
                            ]),
                        ]
                    ]
                ])
            )
            ->build();
    }

    public function actionVacancyUpdateStatus($vacancyId, $isEnabled = false)
    {
        $vacancy = Vacancy::findOne($vacancyId);
        if (!isset($vacancy)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        $vacancy->setAttribute('status', (int)$isEnabled);
        $vacancy->save();

        return $this->actionShow($this->getModelName(Vacancy::class), $vacancyId);
    }

    /**
     * @param $vacancyId
     * @return array
     */
    public function actionVacancyDelete($vacancyId)
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
        } catch (\Throwable $e) {
            Yii::error($e->getMessage());
        }

        return $this->actionVacancyIndex($companyId);
    }

    /**
     * @param int $companyId
     * @return array|ActiveRecord|null
     */
    protected function getCompany(int $companyId) {
        return $this->getUser()->getCompanies()->where([ 'id' => $companyId ])->one();
    }

    /**
     * @param int $vacancyId
     * @return array|ActiveRecord|null
     */
    protected function getVacancy(int $vacancyId) {
        return Vacancy::find()
            ->innerJoinWith(['company'])
            ->innerJoin('{{%company_user}}', '{{%company_user}}.company_id = {{%vacancy}}.company_id')
            ->where([
                '{{%vacancy}}.id' => $vacancyId,
                '{{%company_user}}.user_id' => $this->getUser()->id,
            ])
            ->one();
    }

    /**
     * @param Company $company
     * @return array
     */
    protected function getCompanyKeyboard(Company $company)
    {
        return [
            [
                [
                    'text' => Yii::t('bot', 'Vacancies') . ': ' . $company->getVacancies()->count(),
                    'callback_data' => self::createRoute('vacancy-index', [
                        'companyId' => $company->id,
                    ]),
                ],
            ],
            [
                [
                    'text' => Emoji::BACK,
                    'callback_data' => self::createRoute('company-index'),
                ],
                [
                    'text' => Emoji::MENU,
                    'callback_data' => MenuController::createRoute(),
                ],
                [
                    'text' => Emoji::EDIT,
                    'callback_data' => self::createRoute('update', [
                        'id' => $company->id,
                        'm' => $this->getModelName(Company::class),
                    ]),
                ],
                [
                    'text' => Emoji::DELETE,
                    'callback_data' => self::createRoute('company-delete', [
                        'companyId' => $company->id,
                    ]),
                ],
            ],
        ];
    }

    protected function getVacancyKeyboard(Vacancy $vacancy)
    {
        $isEnabled = $vacancy->status == 1;
        return [
            [
                [
                    'text' => Yii::t('bot', 'Status') . ': ' . Yii::t('bot', $isEnabled ? 'ON' : 'OFF'),
                    'callback_data' => self::createRoute('vacancy-update-status', [
                        'vacancyId' => $vacancy->id,
                        'isEnabled' => !$isEnabled,
                        'test' => 0,
                    ]),
                ],
            ],
            [
                [
                    'text' => '🙋‍♂️ 3',
                    'callback_data' => self::createRoute('show', [
                        'id' => $vacancy->id,
                        'm' => $this->getModelName(Vacancy::class),
                    ]),
                ],
            ],
            [
                [
                    'text' => Emoji::BACK,
                    'callback_data' => self::createRoute('vacancy-index', [
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
                        'id' => $vacancy->id,
                        'm' => $this->getModelName(Vacancy::class),
                    ]),
                ],
                [
                    'text' => Emoji::DELETE,
                    'callback_data' => self::createRoute('vacancy-delete', [
                        'vacancyId' => $vacancy->id
                    ]),
                ],
            ],
        ];
    }

    protected function getCurrencyLabel(Currency $currency)
    {
        return $currency->code . ' - ' . $currency->name;
    }

    /**
     * @param string $className
     */
    protected function beforeCreate(string $className)
    {
        if ($className == Vacancy::class) {
            $this->getState()->setIntermediateField('companyId', $this->getRequest()->getParam('cid', null));
        }
    }

    /**
     * @param ActiveRecord $model
     * @param bool $isNew
     */
    protected function beforeSave(ActiveRecord $model, bool $isNew)
    {
        if ($model instanceof Vacancy) {
            if ($isNew) {
                $model->setAttribute('currency_id', Currency::findOne([ 'code' => 'USD' ])->id);
                $model->setAttribute('company_id', $this->getState()->getIntermediateField('companyId', null));
            }
        }
    }

    protected function afterSave(ActiveRecord $model, bool $isNew)
    {
        if ($model instanceof Company) {
            if ($isNew) {
                $this->getUser()->link('companies', $model);
            }
            return $this->actionShow($this->getModelName(Company::class), $model->id);
        }
        elseif ($model instanceof Vacancy) {
            return $this->actionShow($this->getModelName(Vacancy::class), $model->id);
        }
        return [];
    }

    protected function onCancel(string $className, ?int $id)
    {
        if ($className == Company::class) {
            if (isset($id)) {
                return $this->actionUpdate($this->getModelName($className), $id);
            }
            return $this->actionCompanyIndex();
        } elseif ($className == Vacancy::class) {
            if (isset($id)) {
                return $this->actionUpdate($this->getModelName($className), $id);
            }
            $companyId = $this->getState()->getIntermediateField('companyId', null);
            if (isset($companyId)) {
                return $this->actionVacancyIndex($companyId);
            }
        }
        return parent::onCancel($className, $id);
    }
}
