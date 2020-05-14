<?php
namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\FillablePropertiesController;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use Yii;

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
                        'companyId' => $company->id,
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
                    'text' => Emoji::MENU,
                    'callback_data' => MenuController::createRoute(),
                ],
                [
                    'text' => Emoji::ADD,
                    'callback_data' => self::createRoute('set-property', [
                        'property' => reset(static::$properties),
                    ]),
                ],
            ],
        ]);

        return $this->getResponseBuilder()($update)
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                $keyboards
            )
            ->build();
    }

    public function actionUpdate($companyId)
    {
        return $this->getResponseBuilder()($this->getUpdate())
            ->editMessageReplyMarkup([
                    [
                        [
                            'text' => Yii::t('bot', 'Name'),
                            'callback_data' => self::createRoute('set-property', [
                                'id' => $companyId,
                                'property' => 'name',
                            ]),
                        ],
                    ],
                    [
                        [
                            'text' => Yii::t('bot', 'Description'),
                            'callback_data' => self::createRoute('set-property', [
                                'id' => $companyId,
                                'property' => 'description',
                            ]),
                        ]
                    ],
                    [
                        [
                            'text' => Yii::t('bot', 'Address'),
                            'callback_data' => self::createRoute('set-property', [
                                'id' => $companyId,
                                'property' => 'address',
                            ]),
                        ],
                    ],
                    [
                        [
                            'text' => Yii::t('bot', 'Website'),
                            'callback_data' => self::createRoute('set-property', [
                                'id' => $companyId,
                                'property' => 'url',
                            ]),
                        ],
                    ],
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute('show', [
                                'companyId' => $companyId,
                            ]),
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionShow($companyId)
    {
        $user = $this->getUser();

        $company = $user->getCompanies()->where(['id' => $companyId])->one();
        if (!isset($company)) {
            return [];
        }

        return $this->getResponseBuilder()($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('show', [
                    'name' => $company->name,
                    'url' => $company->url,
                    'address' => $company->address,
                    'description' => $company->description,
                ]),
                [
                    [
                        [
                            'text' => Yii::t('bot', 'Vacancies') . ': ' . $company->getVacancies()->count(),
                            'callback_data' => VacanciesController::createRoute('index', [
                                'companyId' => $companyId,
                            ]),
                        ],
                    ],
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute(),
                        ],
                        [
                            'text' => Emoji::MENU,
                            'callback_data' => MenuController::createRoute(),
                        ],
                        [
                            'text' => Emoji::EDIT,
                            'callback_data' => self::createRoute('update', [
                                'companyId' => $companyId,
                            ]),
                        ],
                    ],
                ],
                true
            )
            ->build();
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
