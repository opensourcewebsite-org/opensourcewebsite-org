<?php

use app\models\Debt;
use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Currency;
use yii\grid\ActionColumn;

/* @var $this yii\web\View */

$currency = Currency::findOne($currencyId);
$this->title = Yii::t('app', $currency->code);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Debts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = '#' . $currencyId;
?>
<div class="debt-view">
    <div class="card">
        <div class="card-header d-flex p-0">
            <ul class="nav nav-pills ml-auto p-2">
                <li class="nav-item">
                    <?= Html::a(Yii::t('app', 'My Deposits'), ['view-user',
                    'direction'  => Debt::DIRECTION_DEPOSIT,
                    'currencyId' => $currencyId,
                    'userId'     => $userId,
                ], [
                        'class' => 'nav-link show ' . ((int) $direction === Debt::DIRECTION_DEPOSIT ? 'active' : ''),
                    ]); ?>
                </li>
                <li class="nav-item">
                    <?= Html::a(Yii::t('app', 'My Credits'), ['view-user',
                    'direction'  => Debt::DIRECTION_CREDIT,
                    'currencyId' => $currencyId,
                    'userId'     => $userId,
                ], [
                        'class' => 'nav-link show ' . ((int) $direction === Debt::DIRECTION_CREDIT ? 'active' : ''),
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
                        'label' => 'User',
                        'value' => function ($data) {
                            return $data->getUserDisplayName();
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
                            'confirm' => function ($url, $data) use ($direction, $currencyId) {
                                return Html::a(Yii::t('app', 'Confirm'), ['debt/confirm', 'id' => $data->id], ['class' => 'btn btn-outline-success']);
                            },
                            'cancel' => function ($url, $data) use ($direction, $currencyId) {
                                return Html::a(Yii::t('app', 'Cancel'), ['debt/cancel', 'id' => $data->id], ['class' => 'btn btn-outline-danger']);
                            },
                        ],
                        'visibleButtons' => [
                            'confirm' => function ($data) {
                                return $data->canConfirm();
                            },
                            'cancel' => function ($data) {
                                return $data->canCancel();
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
