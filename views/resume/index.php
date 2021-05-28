<?php
declare(strict_types=1);

use app\components\helpers\ArrayHelper;
use app\models\Currency;
use app\models\Resume;
use app\models\search\ResumeSearch;
use app\widgets\Alert;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\web\View;
use app\widgets\buttons\AddButton;
use yii\grid\GridView;

/**
 * @var View $this
 * @var ActiveDataProvider $dataProvider
 * @var ResumeSearch $searchModel
 */

$this->title = Yii::t('app', 'Resumes');
$this->params['breadcrumbs'][] = $this->title;

$displayActiveOrders = $searchModel->status === ResumeSearch::STATUS_ON;

?>
<div class="resume-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item mx-1">
                            <?= Html::a(Yii::t('app', 'Active'),
                                ['/resume/index', 'ResumeSearch[status]' => ResumeSearch::STATUS_ON],
                                [
                                    'class' => 'nav-link show ' .
                                        ($displayActiveOrders ? 'active' : '')
                                ]);
                            ?>
                        </li>
                        <li class="nav-item  mx-1">
                            <?= Html::a(Yii::t('app', 'Inactive'),
                                ['/resume/index', 'ResumeSearch[status]' => ResumeSearch::STATUS_OFF],
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
                                    'title' => 'New Resume',
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
                                'attribute' => 'min_hourly_rate',
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'currency_id',
                                'value' => function($model) {
                                    /* @var $model Resume */
                                    return $model->currency_id ? $model->currency->code . ' - ' . $model->currency->name : '';
                                },
                                'filter' => ArrayHelper::map(Currency::find()->asArray()->all(), 'id', 'name'),
                                'enableSorting' => false,
                            ],
                            [
                                'label' => Yii::t('app', 'Offers'),
                                'enableSorting' => false,
                            ],
                            [
                                'class' => ActionColumn::class,
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url) {
                                        $icon = Html::tag('span', '', ['class' => 'fa fa-eye', 'data-toggle' => 'tooltip', 'title' => 'view']);
                                        return Html::a($icon, $url, ['class' => 'btn btn-outline-primary mx-1']);
                                    },
                                    'update' => function ($url) {
                                        $icon = Html::tag('span', '', ['class' => 'fa fa-pen', 'data-toggle' => 'tooltip', 'title' => 'Update']);
                                        return Html::a($icon, $url, ['class' => 'btn btn-outline-primary mx-1']);
                                    },
                                    'delete'
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
