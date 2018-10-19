<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\components\Converter;
use app\models\Rating;
use app\models\User;
use yii\db\Query;

/* @var $this yii\web\View */
$this->title = 'Account';
?>

<div class="user-profile-form">
    <?php echo DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'email',
            [
                'label' => 'Rating',
                'format' => 'html',
                'value' => function ($model) {
                    $maxQuery = (new Query)
                        ->select(['balance' => '(max(balance))'])
                        ->from(Rating::tableName())
                        ->groupBy('user_id');
                    $totalRating = (new Query)
                        ->select(['total' => '(sum(max.balance))'])
                        ->from(['max' => $maxQuery])
                        ->scalar();

                    if ($totalRating == null || $totalRating < 1) {
                        $totalRating = 0;
                        $percent = 0;
                    } else {
                        $percent = Converter::percentage($model->rating, $totalRating);
                    }

                    return "<b>{$model->rating}</b>, {$percent}% of {$totalRating} (total system rating)";
                },
            ],
            [
                'label' => 'Ranking',
                'format' => 'html',
                'value' => function ($model) {
                    $groupQuery = (new Query)
                        ->select([
                            'user_id',
                            'balance' => '(max(balance))'
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
                }
            ],
        ],
    ]) ?>
</div>
<?php if (!Yii::$app->user->isGuest && !Yii::$app->user->identity->is_email_confirmed) : ?>
    <?php echo Html::a('Resend Confirmation Email', ['site/resend-confirmation-email'], ['class' => 'btn btn-primary']); ?>
<?php endif; ?>
