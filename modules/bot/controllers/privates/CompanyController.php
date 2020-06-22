<?php

namespace app\modules\bot\controllers\privates;

use app\models\CompanyUser;
use app\modules\bot\components\CrudController;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\rules\CurrentUserFieldComponent;
use Yii;

use app\models\Company;
use yii\base\DynamicModel;
use yii\data\Pagination;
use yii\db\ActiveRecord;

/**
 * Class CompanyController
 *
 * @package app\modules\bot\controllers\privates
 */
class CompanyController extends CrudController
{
    protected static $properties = [
        'name',
        'description',
        'address',
        'url',
    ];

    /** @inheritDoc */
    protected function rules()
    {
        return [
            [
                'model' => Company::class,
                'relation' => [
                    'model' => CompanyUser::class,
                    'attributes' => [
                        'company_id' => [Company::class, 'id'],
                        'user_id' => [DynamicModel::class, 'id'],
                    ],
                    'component' => [
                        'class' => CurrentUserFieldComponent::class,
                    ],
                ],
                'prepareViewParams' => function ($params) {
                    $model = $params['model'] ?? null;

                    return [
                        'name' => $model->name,
                        'url' => $model->url,
                        'address' => $model->address,
                        'description' => $model->description,
                    ];
                },
                'view' => 'show',
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
     * @param int $page
     *
     * @return array
     */
    public function actionIndex($page = 1)
    {
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
        $keyboards = array_map(function ($company) {
            return [
                [
                    'text' => $company->name,
                    'callback_data' => self::createRoute('view', [
                        'companyId' => $company->id,
                    ]),
                ],
            ];
        }, $companies);
        $keyboards = array_merge($keyboards, [$paginationButtons], [
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
                    'callback_data' => self::createRoute(
                        'create',
                        [
                            'm' => $this->getModelName(Company::class),
                        ]
                    ),
                ],
            ],
        ]);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                $keyboards
            )
            ->build();
    }

    /** @inheritDoc */
    public function actionView($companyId)
    {
        $user = $this->getUser();

        $company = $user->getCompanies()->where(['id' => $companyId])->one();
        if (!isset($company)) {
            return [];
        }

        return $this->getResponseBuilder()
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
                            'callback_data' => VacancyController::createRoute('index', [
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
                            'callback_data' => self::createRoute('u',
                                [
                                    'm' => $this->getModelName(Company::class),
                                    'i' => $companyId,
                                    'b' => 1,
                                ]
                            ),
                        ],
                    ],
                ],
                true
            )
            ->build();
    }

    /**
     * @param array $id
     *
     * @return Company|ActiveRecord
     */
    protected function getModel($id)
    {
        return ($id == null) ? new Company() : Company::findOne($id);
    }
}
