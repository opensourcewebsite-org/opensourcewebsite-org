<?php

use app\models\Debt;
use app\components\helpers\Html;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\widgets\buttons\SelectButton;

/* @var $this yii\web\View */

$this->title = Yii::t('app', 'My deposits') . ' (' . $currency->code . ')';
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Debts')];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'My deposits'), 'url' => ['debt/deposit']];
$this->params['breadcrumbs'][] = ['label' => $currency->code, 'url' => ['debt/currency-deposit', 'currencyId' => $currency->id]];
?>
<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <?= $this->render('_card-header', $_params_); ?>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            [
                                'label' => Yii::t('app', 'User'),
                                'value' => function ($model) {
                                    return Html::a($model->getCounterUserDisplayName(), ['contact/view-user', 'id' => $model->getCounterUserId()]);
                                },
                                'format' => 'html',
                            ],
                            [
                                'label' => Yii::t('app', 'Direction'),
                                'value' => function ($model) {
                                    return $model->getDirection() == Debt::DIRECTION_DEPOSIT ? Yii::t('app', 'My deposit') : Yii::t('app', 'My credit');
                                },
                                'format' => 'html',
                            ],
                            [
                                'label' => Yii::t('app', 'Amount'),
                                'value' => function ($model) {
                                    return $model->amount . ' ' . $model->currency->code;
                                },
                                'format' => 'html',
                            ],
                            [
                                'label' => Yii::t('app', 'Created At'),
                                'value' => function ($model) {
                                    return $model->created_at ?? null;
                                },
                                'format' => 'relativeTime',
                            ],
                            [
                                'value' => function ($model) {
                                    return $model->isStatusPending() ? Html::badge('warning', Yii::t('app', 'Pending')) : '';
                                },
                                'format' => 'html',
                            ],
                            [
                                'class' => ActionColumn::class,
                                'template' => '{confirm} {cancel}',
                                'buttons' => [
                                    'confirm' => function ($url, Debt $model) {
                                        return SelectButton::widget([
                                            'text' => Yii::t('app', 'Confirm'),
                                            'options' => [
                                                'title' => Yii::t('app', 'Confirm'),
                                                'style' => '',
                                                'class' => 'btn btn-outline-success',
                                            ],
                                            'url' => [
                                                'debt/confirm',
                                                'id' => $model->id,
                                            ],
                                        ]);
                                    },
                                    'cancel' => function ($url, Debt $model) {
                                        return SelectButton::widget([
                                            'text' => Yii::t('app', 'Cancel'),
                                            'options' => [
                                                'title' => Yii::t('app', 'Cancel'),
                                                'style' => '',
                                                'class' => 'btn btn-outline-danger',
                                            ],
                                            'url' => [
                                                'debt/cancel',
                                                'id' => $model->id,
                                            ],
                                        ]);
                                    },
                                ],
                                'visibleButtons' => [
                                    'confirm' => function ($model) {
                                        return $model->canConfirm();
                                    },
                                    'cancel' => function ($model) {
                                        return $model->canCancel();
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
