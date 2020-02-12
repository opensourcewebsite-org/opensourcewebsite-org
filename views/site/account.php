<?php

use yii\helpers\Html;
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
                'email',
                [
                    'label' => 'Active Rating',
                    'format' => 'html',
                    'value' => function ($model) use ($activeRating) {
                        return "<b>$activeRating</b>";
                    },
                ],
                [
                    'label' => 'Overall Rating',
                    'format' => 'html',
                    'value' => function ($model) use ($overallRating) {
                        return "<b>$overallRating[rating]</b>, $overallRating[percent]% of $overallRating[totalRating] (total system overall rating)";
                    },
                ],
                [
                    'label' => 'Ranking',
                    'format' => 'html',
                    'value' => function ($model) use ($ranking) {
                        return "#<b>$ranking[rank]</b> among $ranking[total] users";
                    },
                ],
            ],
        ]);
    ?>
    <div class="row">
        <div class="col-md-6">
            <h1>Profile</h1>
        </div>
        <div class="col-md-6">
            <?= Html::a('<i class="fas fa-edit"></i>', ['user/edit-profile'], [
                'class' => 'btn btn-light',
                'title' => 'Edit',
                'style' => ['float' => 'right'],
            ]); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?php echo DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'label' => 'Username',
                        'value' => '@' . $model->username,
                        'visible' => (bool)$model->username
                    ],
                    [
                        'label' => 'Name',
                        'value' => function ($model) {
                            return $model->name ?? $model->id;
                        }
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
