<?php

use app\models\Contact;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
$this->title = Yii::t('app', 'Account');
?>

<div class="user-profile-form">
    <?php
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                [
                    'label' => 'Username',
                    'value' => $model->username,
                    'visible' => (bool)$model->username
                ],
                [
                    'attribute' => 'Real Confirmations',
                    'value' => $realConfirmations
                ],
            ],
        ]);
    ?>
</div>
