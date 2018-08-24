<?php

use app\models\User;
use yii\grid\GridView;

/* @var $this yii\web\View */

$this->title = Yii::t('backend', 'Users');

?>
<div class="user-list">
    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        'options' => [
                ['class' => 'card-body table-responsive'],
        ],
        'tableOptions' => ['class' => 'table table-hover'],
        'columns' => [
            [
                'class' => '\yii\grid\SerialColumn',
            ],
            [
                'label' => 'Email',
                'value' => function ($data) {
                    return $data->email ?? null;
                },
            ],
            [
                'label' => 'Is Email Confirmed?',
                'value' => function ($data) {
                    return $data->is_email_confirmed ? '<i class="fa fa-check"></i>' : '-';
                },
                'format' => 'raw',
            ],
            [
                'label' => 'Status',
                'value' => function ($data) {
                    $status = 'Deleted';
                    if ((int) $data->status === User::STATUS_ACTIVE) {
                        $status = 'Active';
                    }
                    return $status;
                },
            ],
        ],
    ]); ?>
</div>