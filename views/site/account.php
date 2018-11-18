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
                            ->innerJoin(User::tableName() . ' u ON u.id = r.user_id')
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
    <div class="card">
        <div class="card-header d-flex p-0">
            <h3 class="card-title p-3">
                <?= $model->name ?? null; ?>
            </h3>
            <div class="ml-auto p-2">
                <?= Html::a('<i class="fas fa-edit"></i>', ['user/profile'], [
                    'class' => 'btn btn-light',
                    'target' => '_blank',
                    'title' => 'Edit',
                ]) ?>
            </div>
        </div>
    </div>
</div>
<?php if (!Yii::$app->user->isGuest && !Yii::$app->user->identity->is_email_confirmed): ?>
    <?php echo Html::a('Resend Confirmation Email', ['site/resend-confirmation-email'], ['class' => 'btn btn-primary']); ?>
<?php endif;?>
