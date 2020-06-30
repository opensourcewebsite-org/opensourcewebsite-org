<?php

namespace app\modules\bot\controllers\privates;

use app\behaviors\SetAttributeValueBehavior;
use app\models\Currency;
use app\models\Resume;
use app\modules\bot\components\crud\CrudController;
use app\modules\bot\components\crud\rules\LocationToArrayFieldComponent;
use app\modules\bot\components\helpers\ExternalLink;
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
                    /** @var Resume $model */
                    $model = $params['model'] ?? null;

                    return [
                        'model' => $model,
                        'name' => $model->name,
                        'hourlyRate' => $model->min_hourly_rate,
                        'experiences' => $model->experiences,
                        'expectations' => $model->expectations,
                        'skills' => $model->skills,
                        'currencyCode' => $model->currencyCode,
                        'isActive' => $model->isActive(),
                        'remote_on' => $model->remote_on,
                        'locationLink' => ExternalLink::getOSMLink($model->location_lat, $model->location_lon),
                    ];
                },
                'view' => 'show',
                'attributes' => [
                    'name' => [],
                    'skills' => [
                        'isRequired' => false,
                    ],
                    'experiences' => [
                        'isRequired' => false,
                    ],
                    'expectations' => [
                        'isRequired' => false,
                    ],
                    'currency' => [
                        'relation' => [
                            'attributes' => [
                                'currency_id' => [Currency::class, 'id', 'code'],
                            ],
                        ],
                    ],
                    'min_hourly_rate' => [
                        'isRequired' => false,
                        'buttons' => [
                            [
                                'text' => Yii::t('bot', 'Edit currency'),
                                'item' => 'currency',
                            ],
                        ],
                        'prepareViewParams' => function ($params) {
                            /** @var Resume $model */
                            $model = $params['model'];

                            return array_merge($params, [
                                'currencyCode' => $model->currencyCode,
                            ]);
                        },
                    ],
                    'remote_on' => [
                        'buttons' => [
                            [
                                'text' => Yii::t('bot', 'Yes'),
                                'callback' => function (Resume $model) {
                                    $model->remote_on = Resume::REMOTE_ON;

                                    return $model;
                                },
                            ],
                            [
                                'text' => Yii::t('bot', 'No'),
                                'callback' => function (Resume $model) {
                                    $model->remote_on = Resume::REMOTE_OFF;

                                    return $model;
                                },
                            ],
                        ],
                    ],
                    'location' => [
                        'isRequired' => false,
                        'component' => LocationToArrayFieldComponent::class,
                        'buttons' => [
                            [
                                'createMode' => false,
                                'text' => Yii::t('bot', 'My location'),
                                'callback' => function (Resume $model) {
                                    $latitude = $this->getTelegramUser()->location_lat;
                                    $longitude = $this->getTelegramUser()->location_lon;
                                    if ($latitude && $longitude) {
                                        $model->location_lat = $latitude;
                                        $model->location_lon = $longitude;

                                        return $model;
                                    }

                                    return null;
                                },
                            ],
                        ],
                    ],
                    'search_radius' => [
                        'buttons' => [
                            [
                                'text' => Yii::t('bot', 'No radius'),
                                'callback' => function (Resume $model) {
                                    $model->search_radius = 0;

                                    return $model;
                                },
                            ],
                        ],
                    ],
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
                    'text' => ($resume->isActive() ? '' : '❌ ') . $resume->name,
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
                    'model' => $resume,
                    'name' => $resume->name,
                    'hourlyRate' => $resume->min_hourly_rate,
                    'experiences' => $resume->experiences,
                    'expectations' => $resume->expectations,
                    'skills' => $resume->skills,
                    'currencyCode' => $resume->currencyCode,
                    'isActive' => $resume->isActive(),
                    'remote_on' => $resume->remote_on,
                    'locationLink' => ExternalLink::getOSMLink($resume->location_lat, $resume->location_lon),
                ]),
                [
                    [
                        [
                            'text' => Yii::t('bot', 'Status') . ': ' . Yii::t('bot', $isEnabled ? 'ON' : 'OFF'),
                            'callback_data' => self::createRoute('update-status', [
                                'resumeId' => $resumeId,
                                'isEnabled' => !$isEnabled,
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
     * @param integer $id
     *
     * @return Resume|ActiveRecord
     */
    protected function getModel($id)
    {
        return ($id == null) ? new Resume() : Resume::findOne(['id' => $id, 'user_id' => $this->getUser()->id]);
    }
}
