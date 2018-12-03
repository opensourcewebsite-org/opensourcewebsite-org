<?php

use yii\bootstrap\ButtonDropdown;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Support Groups';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="col-md-12">
    <div class="card">
        <div class="card-header text-right">
            <?= Html::a('New Support Group', ['create'], ['class' => 'btn btn-success ml-3']) ?>
        </div>
        <div class="card-body p-0">
            <div class="table table-hover">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'summary' => false,
                    'columns' => [
                        'title',
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{all}',
                            'buttons' => [
                                'all' => function ($url, $model, $key) {
                                    return ButtonDropdown::widget([
                                        'encodeLabel' => false, // if you're going to use html on the button label
                                        'label' => '<i class="fas fa-cog"></i>',
                                        'dropdown' => [
                                            //'encodeLabels' => false, // if you're going to use html on the items' labels
                                            'items' => [
                                                [
                                                    'label' => Yii::t('app', 'Members'),
                                                    'url' => ['members', 'id' => $key],
                                                    'linkOptions' => ['class' => 'dropdown-item']
                                                ],
                                                [
                                                    'label' => Yii::t('app', 'Telegram bots'),
                                                    'url' => ['bots', 'id' => $key],
                                                    'linkOptions' => ['class' => 'dropdown-item']
                                                ],
                                                [
                                                    'label' => Yii::t('app', 'Update'),
                                                    'url' => ['update', 'id' => $key],
                                                    'linkOptions' => ['class' => 'dropdown-item']
                                                ],
                                            ],
                                        ],
                                        'options' => [
                                            'class' => 'btn-default',   // btn-success, btn-info, et cetera
                                        ],
                                        //'split' => true,    // if you want a split button
                                    ]);
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
            <div class="card-footer clearfix">
            </div>
        </div>
    </div>
</div>
