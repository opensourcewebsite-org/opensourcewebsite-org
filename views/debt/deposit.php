<?php

use app\models\Debt;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class="debt-deposit-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $depositDataProvider,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            [
                                'label' => 'User',
                                'value' => function ($data) {
                                    return $data->fromUser->name ?? null;
                                },
                                'format' => 'html',
                            ],
                            [
                                'label' => 'Amount',
                                'value' => function ($data) {
                                    return $data->getDepositAmount();
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
