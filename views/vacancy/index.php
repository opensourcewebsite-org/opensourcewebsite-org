<?php

declare(strict_types=1);

use app\components\helpers\ArrayHelper;
use app\models\Currency;
use app\models\search\VacancySearch;
use app\models\Vacancy;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use app\components\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use app\widgets\buttons\AddButton;
use yii\grid\GridView;

/**
 * @var View $this
 * @var VacancySearch $searchModel
 * @var ActiveDataProvider $dataProvider
 */

$this->title = Yii::t('app', 'Vacancies');
$this->params['breadcrumbs'][] = $this->title;

$displayActiveTab = $searchModel->status === VacancySearch::STATUS_ON;
?>
<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card table-overflow">
                <div class="card-header d-flex p-0">
                    <div class="col-sm-6">
                        <ul class="nav nav-pills ml-auto p-2">
                            <li class="nav-item">
                                <?= Html::a(
    Yii::t('app', 'Active'),
    ['/vacancy/index', 'VacancySearch[status]' => VacancySearch::STATUS_ON],
    [
                                        'class' => 'nav-link show ' . ($displayActiveTab ? 'active' : '')
                                    ]
);
                                ?>
                            </li>
                            <li class="nav-item">
                                <?= Html::a(
                                    Yii::t('app', 'Inactive'),
                                    ['/vacancy/index', 'VacancySearch[status]' => VacancySearch::STATUS_OFF],
                                    [
                                        'class' => 'nav-link show ' . (!$displayActiveTab ? 'active' : ''),
                                    ]
                                );
                                ?>
                            </li>
                        </ul>
                    </div>
                    <div class="col-sm-6">
                        <div class="right-buttons float-right">
                            <?= AddButton::widget([
                                'url' => ['create'],
                                'options' => [
                                    'title' => 'New Vacancy',
                                    'style' => [
                                        'float' => 'right',
                                    ],
                                ],
                            ]); ?>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        //'filterModel' => $searchModel,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'name',
                                'value' => function ($model) {
                                    return $model->name . ($model->company_id ? '<br/><i>' . Html::a($model->company->name, Url::to(['/company-user/view', 'id' => $model->company->id])) . '</i>' : '');
                                },
                                'enableSorting' => false,
                                'format' => 'html',
                            ],
                            [
                                'attribute' => 'max_hourly_rate',
                                'value' => function ($model) {
                                    return $model->max_hourly_rate ? $model->max_hourly_rate . ' ' . $model->currency->code : '∞';
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'label' => Yii::t('app', 'Offers'),
                                'content' => function (Vacancy $model) {
                                    return $model->getMatchesCount() ?
                                        Html::a(
                                            $model->getNewMatchesCount() ? Html::badge('info', 'new') : $model->getMatchesCount(),
                                            Url::to(['/resume/show-matches', 'vacancyId' => $model->id])
                                        ) : '';
                                },
                                'format' => 'raw',
                                'enableSorting' => false,
                                'visible' => $displayActiveTab,
                            ],
                            [
                                'class' => ActionColumn::class,
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url) {
                                        return Html::a(Html::icon('eye'), $url, ['class' => 'btn btn-outline-primary']);
                                    },
                                ],
                            ],
                        ],

                        'layout' => "{summary}\n{items}\n<div class='card-footer clearfix'>{pager}</div>",
                        'pager' => [
                            'options' => [
                                'class' => 'pagination float-right',
                            ],
                            'linkContainerOptions' => [
                                'class' => 'page-item',
                            ],
                            'linkOptions' => [
                                'class' => 'page-link',
                            ],
                            'maxButtonCount' => 5,
                            'disabledListItemSubTagOptions' => [
                                'tag' => 'a',
                                'class' => 'page-link',
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
