<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ButtonDropdown;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Support Group Exchange Rates');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="support-group-exchange-rate-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item align-self-center mr-4">
                            <?= Html::a('<i class="fa fa-plus"></i>', ['support-group-exchange-rate/create', 'supportGroupId' => $supportGroupId], [
                                'class' => 'btn btn-outline-success',
                                'title' => Yii::t('app', 'New Exchange Rate'),
                            ]); ?>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            [
                                'label' => 'Code',
                                'value' => function ($data) {
                                    return $data->code ?? null;
                                },
                                'format' => 'html',
                            ],
                            [
                                'label' => 'Name',
                                'value' => function ($data) {
                                    return $data->name ?? null;
                                },
                                'format' => 'html',
                            ],
                            [
                                'label' => 'Buying Rate',
                                'value' => function ($data) {
                                    return $data->buying_rate ?? null;
                                },
                                'format' => 'html',
                            ],
                            [
                                'label' => 'Selling Rate',
                                'value' => function ($data) {
                                    return $data->selling_rate ?? null;
                                },
                                'format' => 'html',
                            ],
                            [
                                'value' => function ($data) {
                                    return $data->is_default ? 'default' : '';
                                },
                                'format' => 'html',
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{all}',
                                'buttons' => [
                                    'all' => function ($url, $model, $key) use ($supportGroupId) {
                                        return ButtonDropdown::widget([
                                            'encodeLabel' => false,
                                            'label' => '<i class="fas fa-cog"></i>',
                                            'dropdown' => [
                                                'items' => [
                                                    [
                                                        'label' => Yii::t('app', 'Buying Commands'),
                                                        'url' => ['support-group-exchange-rate/index', 'id' => $key],
                                                        'linkOptions' => ['class' => 'dropdown-item'],
                                                    ],
                                                    [
                                                        'label' => Yii::t('app', 'Selling Commands'),
                                                        'url' => ['support-group-exchange-rate/index', 'id' => $key],
                                                        'linkOptions' => ['class' => 'dropdown-item'],
                                                    ],
                                                    [
                                                        'label' => Yii::t('app', 'Edit'),
                                                        'url' => ['support-group-exchange-rate/update', 'id' => $key, 'supportGroupId' => $supportGroupId],
                                                        'linkOptions' => ['class' => 'dropdown-item'],
                                                    ],
                                                ],
                                            ],
                                            'options' => [
                                                'class' => 'btn-default',
                                            ],
                                        ]);
                                    }
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
