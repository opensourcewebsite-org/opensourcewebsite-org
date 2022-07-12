<?php

namespace app\modules\bot\controllers\privates;

use app\models\Company;
use app\models\CompanyUser;
use app\models\scenarios\CompanyUser\DeleteCompanyScenario;
use app\modules\bot\components\crud\CrudController;
use app\modules\bot\components\crud\rules\CurrentUserFieldComponent;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\components\helpers\PaginationButtons;
use Yii;
use yii\base\DynamicModel;
use yii\data\Pagination;
use yii\db\ActiveRecord;

/**
 * Class JoCompanyController
 *
 * @link https://opensourcewebsite.org/company-user
 * @package app\modules\bot\controllers\privates
 */
class JoCompanyController extends CrudController
{
    protected $updateAttributes = [
        'name',
        'description',
        'address',
        'url',
    ];

    /** @inheritDoc */
    protected function rules()
    {
        return [
            'model' => Company::class,
            'prepareViewParams' => function ($params) {
                $model = $params['model'] ?? null;

                return [
                    'model' => $model,
                ];
            },
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
        ];
    }

    /**
     * @param int $page
     *
     * @return array
     */
    public function actionIndex($page = 1)
    {
        $this->getState()->setName(null);

        $globalUser = $this->getUser();

        $query = $globalUser->getCompanies();

        $pagination = new Pagination([
            'totalCount' => $query->count(),
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

        $buttons = [];

        $companies = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($companies) {
            foreach ($companies as $company) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('view', [
                        'id' => $company->id,
                    ]),
                    'text' => $company->name,
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => JoController::createRoute(),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'callback_data' => self::createRoute('create'),
                'text' => Emoji::ADD,
                'visible' => YII_ENV_DEV,
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
     * @param int $id
     *
     * @return array
     */
    public function actionView($id)
    {
        $this->getState()->setName(null);
        $user = $this->getUser();

        $company = $user->getCompanies()
            ->where([
                'id' => $id,
            ])
            ->one();

        if (!isset($company)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('view', [
                    'model' => $company,
                ]),
                [
                    [
                        [
                            'text' => Emoji::JO_VACANCY . ' ' . Yii::t('bot', 'Vacancies'),
                            'callback_data' => JoVacancyController::createRoute('index', [
                                'companyId' => $company->id,
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
                                'id' => $company->id,
                            ]),
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @param int $id
     */
    public function actionDelete($id)
    {
        $user = $this->getUser();

        /** @var Company $company */
        $company = $user->getCompanies()
            ->where([
                'id' => $id,
            ])
            ->one();

        if (!isset($company)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $deleteScenario = new DeleteCompanyScenario($company);

        if (!$deleteScenario->run()) {
            return $this->getResponseBuilder()->sendMessage(
                new MessageText($deleteScenario->getFirstError())
            );
        }

        return $this->actionIndex();
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
