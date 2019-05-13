<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
$this->title = 'Account';
?>

<div class="user-profile-form">
    <?php
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'name',
                [
                    'label' => 'Username',
                    'value' => $model->username,
                    'visible' => (bool)$model->username
                ],
            ],
        ]);
    ?>
</div>