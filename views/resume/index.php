<?php

use app\models\Resume;
use app\models\search\ResumeSearch;
use yii\data\ActiveDataProvider;
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
<div class="currency-exchange-order-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Active'),
                                ['/resume/index', 'ResumeSearch[status]' => ResumeSearch::STATUS_ON],
                                [
                                    'class' => 'nav-link show ' .
                                        ($displayActiveOrders ? 'active' : '')
                                ]);
                            ?>
                        </li>
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Inactive'),
                                ['/resume/index', 'ResumeSearch[status]' => ResumeSearch::STATUS_OFF],
                                [
                                    'class' => 'nav-link show ' .
                                        ($displayActiveOrders ? '' : 'active')
                                ]);
                            ?>
                        </li>
                        <li class="nav-item align-self-center mr-4">
                            <?= AddButton::widget([
                                'url' => ['resume/create'],
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
                        'filterModel' => $searchModel,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            'name',
                            'min_hourly_rate',
                            'search_radius',
                            [
                                'attribute' => 'currency_id',
                                'value' => function($model) {
                                    /* @var $model Resume */
                                    return $model->currency->name;
                                }
                            ],
                            [
                                'label' => 'offers'
                            ]
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

