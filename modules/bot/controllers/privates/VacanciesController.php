<?php
namespace app\modules\bot\controllers\privates;

use Yii;
use app\models\Vacancy;
use app\modules\bot\components\FillablePropertiesController;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;
use yii\db\ActiveRecord;

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
                            'callback_data' => self::createRoute('update', [
                                'id' => $id
                            ]),
                        ],
                    ],
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => CompaniesController::createRoute('show', [
                                'id' => $vacancy->company->id
                            ]),
                        ],
                        [
                            'text' => Yii::t('bot', 'Publish'),
                            'callback_data' => self::createRoute('publish', [
                                'id' => $id
                            ]),
                        ],
                    ],
                ]
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
                        'text' => Yii::t('bot', 'Edit employment'),
                        'callback_data' => self::createRoute('set-property', [
                            'id' => $id,
                            'property' => 'employment',
                        ]),
                    ],
                ],
                [
                    [
                        'text' => Yii::t('bot', 'Edit hours of employment'),
                        'callback_data' => self::createRoute('set-property', [
                            'id' => $id,
                            'property' => 'hours-of-employment',
                        ]),
                    ],
                    [
                        'text' => Yii::t('bot', 'Edit salary'),
                        'callback_data' => self::createRoute('set-property', [
                            'id' => $id,
                            'property' => 'salary',
                        ]),
                    ],
                ],
                [
                    [
                        'text' => Yii::t('bot', 'Edit requirements'),
                        'callback_data' => self::createRoute('set-property', [
                            'id' => $id,
                            'property' => 'requirements',
                        ]),
                    ],
                    [
                        'text' => Yii::t('bot', 'Edit skills description'),
                        'callback_data' => self::createRoute('set-property', [
                            'id' => $id,
                            'property' => 'skills-description',
                        ]),
                    ],
                ],
                [
                    [
                        'text' => Yii::t('bot', 'Edit conditions'),
                        'callback_data' => self::createRoute('set-property', [
                            'id' => $id,
                            'property' => 'conditions',
                        ]),
                    ],
                    [
                        'text' => Yii::t('bot', 'Edit responsibility'),
                        'callback_data' => self::createRoute('set-property', [
                            'id' => $id,
                            'property' => 'responsibility',
                        ]),
                    ],
                ],
                [
                    [
                        'text' => Emoji::BACK,
                        'callback_data' => self::createRoute('show', [
                            'id' => $id,
                        ]),
                    ],
                ],
            ])
            ->build();
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
            ]);
    }
}
