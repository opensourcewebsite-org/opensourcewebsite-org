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
                    <?= Html::a(Yii::t('app', 'Deposits'), ['debt/view', 'direction' => Debt::DIRECTION_DEPOSIT, 'currencyId' => $currencyId], [
                        'class' => 'nav-link show ' . ((int) $direction === Debt::DIRECTION_DEPOSIT ? 'active' : ''),
                    ]); ?>
                </li>
                <li class="nav-item">
                    <?= Html::a(Yii::t('app', 'Credits'), ['debt/view', 'direction' => Debt::DIRECTION_CREDIT, 'currencyId' => $currencyId], [
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
                        'label' => 'Valid',
                        'value' => function ($data) {
                            return $data->valid_from_date ?? null;
                        },
                        'format' => 'relativeTime',
                    ],
                    [
                        'value' => function ($data) {
                            $status = '<span class="badge badge-warning">Pending</span>';
                            if ((int) $data->status === Debt::STATUS_CONFIRM) {
                                $status = '<span class="badge badge-success">Confirm</span>';
                            }
                            return $status;
                        },
                        'format' => 'html',
                    ],
                    [
                        'class' => ActionColumn::class,
                        'template' => '{confirm} {delete}',
                        'buttons' => [
                            'confirm' => function ($url, $data) use ($direction, $currencyId) {
                                return Html::a('Confirm', ['debt/confirm', 'id' => $data->id, 'direction' => $direction, 'currencyId' => $currencyId], ['class' => 'btn btn-outline-success',]);
                            },
                            'delete' => function ($url) {
                                return Html::a('Cancel', $url, ['id' => 'delete-debt', 'class' => 'btn btn-outline-danger',]);
                            },
                        ],
                        'visibleButtons' => [
                            'confirm' => function ($data) use ($direction) {
                                return $data->canConfirmDebt($direction);
                            },
                            'delete' => function ($data) {
                                return ((int) $data->from_user_id === Yii::$app->user->id) || ((int) $data->to_user_id === Yii::$app->user->id);
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
<?php $this->registerJs('$("#delete-debt").on("click", function(event) {
    event.preventDefault();
    var url = $(this).attr("href");

    if (confirm("' . Yii::t('app', 'Are you sure you want to cancel this debt?') . '")) {
        $.post(url, {}, function(result) {
            if (result == "1") {
                location.href = "' . Yii::$app->urlManager->createUrl(['/debt']) . '";
            }
            else {
                alert("' . Yii::t('app', 'Sorry, there was an error while trying to cancel the debt.') . '");
            }
        });
    }
    
    return false;
});'); ?>