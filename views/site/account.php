<?php

use yii\helpers\Html;
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
                'email',
                [
                    'label' => 'Rank',
                    'format' => 'html',
                    'value' => function ($model) use ($ranking) {
                        return "<b>$ranking[rank]</b> of $ranking[total]";
                    },
                ],
                [
                    'label' => 'Voting Power',
                    'format' => 'html',
                    'value' => function ($model) use ($overallRating) {
                        return "<b>$overallRating[percent]%</b> of 100%";
                    },
                ],
                [
                    'label' => 'Rating',
                    'format' => 'html',
                    'value' => function ($model) use ($overallRating) {
                        return "<b>$overallRating[rating]</b> of $overallRating[totalRating]";
                    },
                ],
                [
                    'label' => 'Active Rating',
                    'format' => 'html',
                    'value' => function ($model) use ($activeRating) {
                        return "<b>$activeRating</b> (" . Yii::t('bot', 'in the last {0,number} days', 30) . ')' ;
                    },
                ],
            ],
        ]);
    ?>
    <div class="row">
        <div class="col-md-6">
            <h2><?= Yii::t('app', 'Profile') ?></h2>
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
