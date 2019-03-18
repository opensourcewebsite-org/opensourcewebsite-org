<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\WikiPage;
use app\components\TitleColumn;

/* @var View $this */
/* @var string $title */
/* @var ActiveDataProvider $dataProvider */

$this->title = $title;

?>
<div class="card">
    <div class="card-body p-0">
        <?php if (Yii::$app->request->get('code')): ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'summary' => false,
                'tableOptions' => ['class' => 'table table-hover'],
                'columns' => [
                    [
                        'class' => TitleColumn::class,
                        'attribute' => 'title',
                        'value' => function (WikiPage $model) {
                            return Html::a(
                                    $model->title, $model->wikiUrl, ['target' => '_blank']
                            );
                        },
                        'label' => 'Title',
                        'title' => 'Titles of Wikipedia pages',
                        'format' => 'raw',
                    ],
                    [
                        'class' => TitleColumn::class,
                        'label' => 'Rating',
                        'title' => 'The rating of Wikipedia pages. The rating adds 1 to the page from each user who watch the page and adds value of VIP level from same user.',
                        'value' => 'rating',
                    ],
                ],
            ]); ?>
        <?php endif ?>
    </div>
</div>