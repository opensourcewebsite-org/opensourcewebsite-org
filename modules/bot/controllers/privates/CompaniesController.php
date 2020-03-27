<?php
namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\FillablePropertiesController;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use Yii;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\response\ResponseBuilder;
use app\models\Company;
use yii\data\Pagination;

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
            'pageSize' => 8,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);
        $paginationButtons = PaginationButtons::build(self::createRoute() . ' ', $pagination);
        $companies = $user->getCompanies()
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $keyboards = array_map(function($company) {
            return [
                [
                    'text' => $company->name,
                    'callback_data' => self::createRoute('show', [ $company->id ]),
                ],
            ];
        }, $companies);
        $keyboards = array_merge($keyboards, [ $paginationButtons ], [
            [
                [
                    'text' => Emoji::ADD,
                    'callback_data' => self::createRoute('set_' . reset(static::$properties)),
                ],
            ],
        ]);

        return ResponseBuilder::fromUpdate($update)
            ->answerCallbackQuery()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                $keyboards,
                HrController::createRoute(),
                true
            )
            ->build();
	}

    public function actionUpdate($id)
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->editMessageReplyMarkup([
                    [
                        [
                            'text' => Yii::t('bot', 'Edit name'),
                            'callback_data' => self::createRoute('set_name', [ $id ]),
                        ],
                        [
                            'text' => Yii::t('bot', 'Edit address'),
                            'callback_data' => self::createRoute('set_address', [ $id ]),
                        ],
                    ],
                    [
                        [
                            'text' => Yii::t('bot', 'Edit website link'),
                            'callback_data' => self::createRoute('set_url', [ $id ]),
                        ],
                        [
                            'text' => Yii::t('bot', 'Edit description'),
                            'callback_data' => self::createRoute('set_description', [ $id ]),
                        ]
                    ],
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute('show', [ $id ]),
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
                'pageSize' => 7,
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
            $paginationButtons = PaginationButtons::build(self::createRoute('show', [ $id ]) . ' ', $pagination);
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
                ->answerCallbackQuery()
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
                                'callback_data' => VacanciesController::createRoute('create', [ $id ]),
                            ],
                        ],
                        [
                            [
                                'text' => Emoji::EDIT,
                                'callback_data' => self::createRoute('update', [ $id ]),
                            ],
                        ],
                    ]),
                    self::createRoute(),
                    true
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

    protected function afterSave($company, $isNew)
    {
        if ($isNew) {
            $this->getUser()->link('companies', $company);
        }
        return $this->actionShow($company->id);
    }
}
