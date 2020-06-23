<?php

namespace app\modules\bot\controllers\privates;

use app\behaviors\SetAttributeValueBehavior;
use app\models\Currency;
use app\models\Resume;
use app\modules\bot\components\CrudController;
use app\modules\bot\components\helpers\PaginationButtons;
use Yii;
use app\modules\bot\components\helpers\Emoji;
use yii\data\Pagination;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;

/**
 * Class ResumeController
 *
 * @package app\modules\bot\controllers\privates
 */
class ResumeController extends CrudController
{
    /** @inheritDoc */
    protected function rules()
    {
        return [
            [
                'model' => Resume::class,
                'prepareViewParams' => function ($params) {
                    $model = $params['model'] ?? null;

                    return [
                        'name' => $model->name,
                        'hourlyRate' => $this->getDisplayHourlyRate($model),
                        'experiences' => $model->experiences,
                        'expectations' => $model->expectations,
                        'skills' => $model->skills,
                        'currency' => $model->currency,
                    ];
                },
                'view' => 'show',
                'attributes' => [
                    'name' => [],
                    'min_hourly_rate' => [
                        'isRequired' => false,
                    ],
                    'currency' => [
                        'relation' => [
                            'attributes' => [
                                'currency_id' => [Currency::class, 'id', 'code'],
                            ],
                        ],
                    ],
                    'skills' => [],
                    'experiences' => [],
                    'expectations' => [],
                    'user_id' => [
                        'behaviors' => [
                            'SetAttributeValueBehavior' => [
                                'class' => SetAttributeValueBehavior::class,
                                'attributes' => [
                                    ActiveRecord::EVENT_BEFORE_VALIDATE => ['user_id'],
                                    ActiveRecord::EVENT_BEFORE_INSERT => ['user_id'],
                                ],
                                'attribute' => 'user_id',
                                'value' => $this->module->user->id,
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
     * @param int $page
     *
     * @return array
     */
    public function actionIndex($page = 1)
    {
        $user = $this->getUser();
        $resumesCount = $user->getResumes()->count();

        $pagination = new Pagination([
            'totalCount' => $resumesCount,
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
        $resumes = $user->getResumes()
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $keyboards = array_map(function ($resume) {
            return [
                [
                    'text' => $resume->name,
                    'callback_data' => self::createRoute('view', [
                        'resumeId' => $resume->id,
                    ]),
                ],
            ];
        }, $resumes);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'vacanciesCount' => $resumesCount,
                ]),
                array_merge($keyboards, [$paginationButtons], [
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
                            'callback_data' => ResumeController::createRoute(
                                'create',
                                [
                                    'm' => $this->getModelName(Resume::class),
                                ]
                            ),
                        ],
                    ],
                ])
            )
            ->build();
    }

    /** @inheritDoc */
    public function actionView($resumeId)
    {
        $resume = Resume::findOne($resumeId);
        if (!isset($resume)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $isEnabled = $resume->status == 1;

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('show', [
                    'name' => $resume->name,
                    'hourlyRate' => $this->getDisplayHourlyRate($resume),
                    'experiences' => $resume->experiences,
                    'expectations' => $resume->expectations,
                    'skills' => $resume->skills,
                    'currency' => $resume->currency,
                ]),
                [
                    [
                        [
                            'text' => Yii::t('bot', 'Status') . ': ' . Yii::t('bot', $isEnabled ? 'ON' : 'OFF'),
                            'callback_data' => self::createRoute('update-status', [
                                'resumeId' => $resumeId,
                                'isEnabled' => !$isEnabled,
                                'test' => 0,
                            ]),
                        ],
                    ],
                    [
                        [
                            'text' => '🙋‍♂️ 3',
                            'callback_data' => self::createRoute('view', [
                                'resumeId' => $resumeId,
                            ]),
                        ],
                    ],
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute('index'),
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
                                    'm' => $this->getModelName(Resume::class),
                                    'i' => $resumeId,
                                ]
                            ),
                        ],
                        [
                            'text' => Emoji::DELETE,
                            'callback_data' => self::createRoute('delete', [
                                'resumeId' => $resumeId,
                            ]),
                        ],
                    ],
                ],
                true
            )
            ->build();
    }

    /**
     * @param $resumeId
     *
     * @return array
     */
    public function actionDelete($resumeId)
    {
        $resume = Resume::findOne(['id' => $resumeId, 'user_id' => $this->getUser()->id]);
        if (!isset($resume)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }
        try {
            $resume->delete();
        } catch (StaleObjectException $e) {
        } catch (\Throwable $e) {
        }

        return $this->actionIndex();
    }

    /**
     * @param $resumeId
     * @param bool $isEnabled
     *
     * @return array
     */
    public function actionUpdateStatus($resumeId, $isEnabled = false)
    {
        $resume = Resume::findOne($resumeId);
        if (!isset($resume)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $resume->setAttribute('status', (int)$isEnabled);
        $resume->save();

        return $this->actionView($resumeId);
    }

    /**
     * @param Resume $resume
     *
     * @return string|null
     */
    private function getDisplayHourlyRate(Resume $resume)
    {
        if ($resume->min_hourly_rate) {
            return Yii::t('bot', 'from') . " {$resume->min_hourly_rate}";
        }

        return null;
    }

    /**
     * @param integer $id
     *
     * @return Resume|ActiveRecord
     */
    protected function getModel($id)
    {
        return ($id == null) ? new Resume() : Resume::findOne(['id' => $id, 'user_id' => $this->getUser()->id]);
    }
}
