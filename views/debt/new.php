<?php

use app\models\Debt;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\widgets\buttons\AddButton;

/* @var $this yii\web\View */

$this->title = Yii::t('app', 'New transactions');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Debts'), 'url' => ['index']];

?>

<div class="callout callout-danger">
    <h5><?= Yii::t('app', 'Attention') ?>!</h5>
    <p>
        <?= Yii::t('app', 'This feature works in test mode') ?>. <?= Yii::t('app', 'Please help to test all functions of this') ?>. <?= Yii::t('app', 'All data of debts will be deleted from 2020-08-01 or earlier') ?>. <?= Yii::t('app', 'After that, this feature will work in an operating mode') ?>.
    </p>
</div>

<div class="debt-view">
    <div class="card">
    <div class="card-header">
                    <div class="row">
                        <div class="col-sm-6">
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                    <?php 
                                        echo Html::a(Yii::t('app', 'New'), ['debt/index',], [
                                            'class' => 'btn btn-outline-primary mr-2 active',
                                        ]); 
                                    ?>
                                </li>
                                <li class="nav-item">
                                    <?= Html::a(Yii::t('app', 'Current'), ['debt/current',], [
                                        'class' => 'btn btn-outline-primary mr-2',
                                    ]); ?>
                                </li>
                                <li class="nav-item">
                                    <?= Html::a(Yii::t('app', 'History'), ['debt/history',], [
                                        'class' => 'btn btn-outline-primary',
                                    ]); ?>
                                </li>
                            </ul>
                        </div>
                        <div class="col-sm-6">
                            <div class="right-buttons float-right">
                                <?= AddButton::widget([
                                    'url' => 'debt/create',
                                    'options' => [
                                        'title' => 'New debt',
                                    ]
                                ]); ?>
                            </div>
                        </div>
                    </div>
                </div>
        <div class="card-body p-0">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'summary' => false,
                'tableOptions' => ['class' => 'table table-hover'],
                'columns' => [
                    [
                        'label' => 'User',
                        'value' => function ($data) use ($direction) {
                            return $data->getUserDisplayName($direction);
                        },
                        'format' => 'html',
                    ],
                    [
                        'label' => 'Amount',
                        'value' => function ($data) {
                            return $data->amount ?? null;
                        },
                        'format' => 'html',
                    ],
                    [
                        'label' => 'Created At',
                        'value' => function ($data) {
                            return $data->created_at ?? null;
                        },
                        'format' => 'relativeTime',
                    ],
                    [
                        'value' => function (Debt $data) {
                            if ($data->isStatusPending()) {
                                return '<span class="badge badge-warning">Pending</span>';
                            }
                            return '';
                        },
                        'format' => 'html',
                    ],
                    [
                        'class' => ActionColumn::class,
                        'template' => '{confirm} {cancel}',
                        'buttons' => [
                            'confirm' => function ($url, $data) use ($direction) {
                                return Html::a('Confirm', ['debt/confirm', 'id' => $data->id, 'direction' => $direction, 'currencyId' => $data->currency_id], ['class' => 'btn btn-outline-success']);
                            },
                            'cancel' => function ($url, $data) use ($direction) {
                                return Html::a('Cancel', ['debt/cancel', 'id' => $data->id, 'direction' => $direction, 'currencyId' => $data->currency_id], ['class' => 'btn btn-outline-danger']);
                            },
                        ],
                        'visibleButtons' => [
                            'confirm' => function ($data) use ($direction) {
                                return $data->canConfirmDebt($direction);
                            },
                            'cancel' => function ($data) {
                                return $data->canCancelDebt();
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
