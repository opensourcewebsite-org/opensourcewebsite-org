<?php
namespace app\modules\bot\controllers\privates;

use Yii;
use app\models\Vacancy;
use app\modules\bot\components\FillablePropertiesController;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;

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
            'responsibility',
        ];

    public function actionCreate($id)
    {
        $this->getState()->setIntermediateField('companyId', $id);
        return $this->actionSetProperty(reset(static::$properties));
    }

    public function actionShow($id)
    {
        $vacancy = Vacancy::findOne($id);
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->editMessageTextOrSendMessage(
                $this->render('show', [
                    'name' => $vacancy->name,
                    'employment' => $vacancy->employment,
                    'hoursOfEmployment' => $vacancy->hours_of_employment,
                    'salary' => $vacancy->salary,
                    'requirements' => $vacancy->requirements,
                    'skillsDescription' => $vacancy->skills_description,
                    'conditions' => $vacancy->conditions,
                    'responsibility' => $vacancy->responsibility,
                    'views' => $vacancy->views,
                    'status' => $vacancy->status,
                ]),
                [
                    [
                        [
                            'text' => Emoji::EDIT,
                            'callback_data' => self::createRoute('update', [ $id ]),
                        ],
                    ],
                    [
                        [
                            'text' => Yii::t('bot', 'Publish'),
                            'callback_data' => self::createRoute('publish', [ $id ]),
                        ],
                    ],
                ],
                CompaniesController::createRoute('show', [ $vacancy->company->id ]),
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
                        'text' => Yii::t('bot', 'Edit employment'),
                        'callback_data' => self::createRoute('set_employment', [ $id ]),
                    ],
                ],
                [
                    [
                        'text' => Yii::t('bot', 'Edit hours of employment'),
                        'callback_data' => self::createRoute('set_hours_of_employment', [ $id ]),
                    ],
                    [
                        'text' => Yii::t('bot', 'Edit salary'),
                        'callback_data' => self::createRoute('set_salary', [ $id ]),
                    ],
                ],
                [
                    [
                        'text' => Yii::t('bot', 'Edit requirements'),
                        'callback_data' => self::createRoute('set_requirements', [ $id ]),
                    ],
                    [
                        'text' => Yii::t('bot', 'Edit skills description'),
                        'callback_data' => self::createRoute('set_skills_description', [ $id ]),
                    ],
                ],
                [
                    [
                        'text' => Yii::t('bot', 'Edit conditions'),
                        'callback_data' => self::createRoute('set_conditions', [ $id ]),
                    ],
                    [
                        'text' => Yii::t('bot', 'Edit responsibility'),
                        'callback_data' => self::createRoute('set_responsibility', [ $id ]),
                    ],
                ],
                [
                    [
                        'text' => Emoji::BACK,
                        'callback_data' => self::createRoute('show', [ $id ]),
                    ],
                ],
            ])
            ->build();
    }

    protected function afterSave($model, $isNew)
    {
        return $this->actionShow($model->id);
    }

    protected function getModel($id)
    {
        return !is_null($id)
            ? Vacancy::findOne($id)
            : new Vacancy([
                'company_id' => $this->getState()->getIntermediateField('companyId', null),
            ]);
    }
}
