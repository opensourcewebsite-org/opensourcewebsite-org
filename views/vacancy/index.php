<?php

use app\components\helpers\ArrayHelper;
use app\models\Currency;
use app\models\search\VacancySearch;
use app\models\Vacancy;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\helpers\Html;
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

$displayActiveOrders = $searchModel->status === VacancySearch::STATUS_ON;

?>
<div class="vacancy-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item mx-1">
                            <?= Html::a(Yii::t('app', 'Active'),
                                ['/vacancy/index', 'VacancySearch[status]' => VacancySearch::STATUS_ON],
                                [
                                    'class' => 'nav-link show ' .
                                        ($displayActiveOrders ? 'active' : '')
                                ]);
                            ?>
                        </li>
                        <li class="nav-item  mx-1">
                            <?= Html::a(Yii::t('app', 'Inactive'),
                                ['/vacancy/index', 'VacancySearch[status]' => VacancySearch::STATUS_OFF],
                                [
                                    'class' => 'nav-link show ' .
                                        ($displayActiveOrders ? '' : 'active')
                                ]);
                            ?>
                        </li>
                        <li class="nav-item align-self-center mr-4  mx-1">
                            <?= AddButton::widget([
                                'url' => ['create'],
                                'options' => [
                                    'title' => 'New Vacancy',
                                ]
                            ]); ?>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        //'filterModel' => $searchModel,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            'id',
                            [
                                'attribute' => 'name',
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'max_hourly_rate',
                                'value' => function($model) {
                                    return $model->max_hourly_rate ? $model->max_hourly_rate . ' ' . $model->currency->code : '∞';
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'label' => Yii::t('app', 'Offers'),
                                'enableSorting' => false,
                                'format' => 'raw',
                                'content' => function (Vacancy $model){
                                    return $model->getMatches()->count() ?
                                        Html::a(
                                            $model->getMatches()->count(),
                                            Url::to(['/resume/show-matches', 'vacancyId' => $model->id]),
                                        ) : '';
                                }
                            ],
                            [
                                'class' => ActionColumn::class,
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url) {
                                        $icon = Html::tag('span', '', ['class' => 'fa fa-eye', 'data-toggle' => 'tooltip', 'title' => 'view']);
                                        return Html::a($icon, $url, ['class' => 'btn btn-outline-primary mx-1']);
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
