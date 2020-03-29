<?php
namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\FillablePropertiesController;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use Yii;
use app\modules\bot\components\response\ResponseBuilder;
use app\models\Company;
use yii\data\Pagination;
use yii\db\ActiveRecord;

class CompaniesController extends FillablePropertiesController
{
    protected static $properties = [
            'name',
            'description',
            'address',
            'url'
        ];

    public function actionIndex($page = 1)
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        $companiesCount = $user->getCompanies()->count();
        $pagination = new Pagination([
            'totalCount' => $companiesCount,
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);
        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('index', [
                'page' => $page,
            ]);
        });
        $companies = $user->getCompanies()
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $keyboards = array_map(function($company) {
            return [
                [
                    'text' => $company->name,
                    'callback_data' => self::createRoute('show', [
                        'id' => $company->id,
                    ]),
                ],
            ];
        }, $companies);
        $keyboards = array_merge($keyboards, [ $paginationButtons ], [
            [
                [
                    'text' => Emoji::BACK,
                    'callback_data' => SJobController::createRoute(),
                ],
                [
                    'text' => Emoji::ADD,
                    'callback_data' => self::createRoute('set-property', [
                        'property' => reset(static::$properties),
                    ]),
                ],
            ],
        ]);

        return ResponseBuilder::fromUpdate($update)
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                $keyboards
            )
            ->build();
    }

    public function actionUpdate($id)
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageReplyMarkup([
                    [
                        [
                            'text' => Yii::t('bot', 'Edit name'),
                            'callback_data' => self::createRoute('set-property', [
                                'id' => $id,
                                'property' => 'name',
                            ]),
                        ],
                        [
                            'text' => Yii::t('bot', 'Edit address'),
                            'callback_data' => self::createRoute('set-property', [
                                'id' => $id,
                                'property' => 'address',
                            ]),
                        ],
                    ],
                    [
                        [
                            'text' => Yii::t('bot', 'Edit website link'),
                            'callback_data' => self::createRoute('set-property', [
                                'id' => $id,
                                'property' => 'url',
                            ]),
                        ],
                        [
                            'text' => Yii::t('bot', 'Edit description'),
                            'callback_data' => self::createRoute('set-property', [
                                'id' => $id,
                                'property' => 'description',
                            ]),
                        ]
                    ],
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute('show', [
                                'id' => $id
                            ]),
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionShow($id, $page = 1)
    {
        $user = $this->getUser();
        $update = $this->getUpdate();

        $company = $user->getCompanies()->where(['id' => $id])->one();
        if ($company != null) {
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
            $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($id) {
                return self::createRoute('show', [
                    'id' => $id,
                    'page' => $page,
                ]);
            });
            $rows = array_map(function ($vacancy) {
                return [
                    [
                        'text' => $vacancy->name,
                        'callback_data' => VacanciesController::createRoute('show', [ $vacancy->id ]),
                    ]
                ];
            }, $vacancies);
            $rows = array_merge($rows, [ $paginationButtons ]);

            return ResponseBuilder::fromUpdate($update)
                ->editMessageTextOrSendMessage(
                    $this->render('show', [
                        'name' => $company->name,
                        'url' => $company->url,
                        'address' => $company->address,
                        'description' => $company->description,
                        'vacanciesCount' => $vacanciesCount,
                    ]),
                    array_merge($rows, [
                        [
                            [
                                'text' => Yii::t('bot', 'Add a vacancy'),
                                'callback_data' => VacanciesController::createRoute('create', [
                                    'id' => $id,
                                ]),
                            ],
                        ],
                        [
                            [
                                'text' => Emoji::BACK,
                                'callback_data' => self::createRoute(),
                            ],
                            [
                                'text' => Emoji::EDIT,
                                'callback_data' => self::createRoute('update', [
                                    'od'
                                ]),
                            ],
                        ],
                    ])
                )
                ->build();
        } else {
            return [];
        }
    }

    protected function getModel($id)
    {
        return ($id == null) ? new Company() : Company::findOne($id);
    }

    /**
     * @param ActiveRecord $company
     * @param bool $isNew
     * @return array
     */
    protected function afterSave(ActiveRecord $company, bool $isNew)
    {
        if ($isNew) {
            $this->getUser()->link('companies', $company);
        }
        return $this->actionShow($company->id);
    }
}
