<?php

use app\components\Converter;
use app\models\Rating;
use app\models\User;
use yii\db\Query;
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
                    'value' => function ($model) {
                        return "<b>{$model->activeRating}</b>";
                    },
                ],
                [
                    'label' => 'Overall Rating',
                    'format' => 'html',
                    'value' => function ($model) use ($totalRating) {

                        if ($totalRating < 1) {
                            $percent = 0;
                        } else {
                            $percent = Converter::percentage($model->rating, $totalRating);
                        }

                        return "<b>{$model->rating}</b>, {$percent}% of {$totalRating} (total system overall rating)";
                    },
                ],
                [
                    'label' => 'Ranking',
                    'format' => 'html',
                    'value' => function ($model) {
                        $groupQuery = (new Query)
                            ->select([
                                'user_id',
                                'balance' => '(sum(amount))',
                            ])
                            ->from(Rating::tableName() . ' r')
                            ->groupBy('user_id')
                            ->orderBy('balance DESC');

                        $total = $groupQuery->count();

                        $rank = (new Query)
                            ->select(['count(*)+1'])
                            ->from(['g' => $groupQuery])
                            ->where(['>', 'balance', $model->rating])
                            ->scalar();

                        return "#<b>{$rank}</b> among {$total} users";
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
