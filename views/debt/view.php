<?php

use app\models\Debt;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\ActionColumn;

/* @var $this yii\web\View */
/* @var $model app\models\Debt */

$this->title = Yii::t('app', $model->currency->code);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Debts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = '#' . $model->id;

?>
<div class="debt-view">
    <div class="card">
        <div class="card-header d-flex p-0">
            <ul class="nav nav-pills ml-auto p-2">
                <li class="nav-item">
                    <?= Html::a(Yii::t('app', 'Deposits'), ['debt/view', 'id' => $model->id, 'direction' => Debt::DIRECTION_DEPOSIT, 'currencyId' => $model->currency_id], [
                        'class' => 'nav-link show ' . ((int) $direction === Debt::DIRECTION_DEPOSIT ? 'active' : ''),
                    ]); ?>
                </li>
                <li class="nav-item">
                    <?= Html::a(Yii::t('app', 'Credits'), ['debt/view', 'id' => $model->id, 'direction' => Debt::DIRECTION_CREDIT, 'currencyId' => $model->currency_id], [
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
                        'template' => '{delete}',
                        'buttons' => [
                            'delete' => function ($url) {
                                return Html::a('Cancel', $url, ['id' => 'delete-debt', 'class' => 'btn btn-outline-danger',]);
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