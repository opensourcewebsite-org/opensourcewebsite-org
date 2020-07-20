<?php

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = $language->name . ' ' . Yii::t('app', 'wikipedia missing pages');

?>
<div class="card">
    <div class="card-body p-0">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'summary' => false,
            'tableOptions' => ['class' => 'table table-hover'],
            'columns' => [
                [
                    'attribute' => 'title',
                    'headerOptions' => ['title' => 'Title of wikipedia pages'],
                    'value' => function ($model) {
                        return Html::a($model->title, $model->wikiUrl, ['linkOptions' => ['target' => '_blank']]);
                    },
                    'format' => 'raw',
                ],
                [
                    'label' => 'Rating',
                    'headerOptions' => [
                        'title' => 'The rating of Wikipedia pages. The rating adds 1 to the page from each user who watch the page and adds value of VIP level from same user.',
                    ],
                    'value' => function ($model) {
                        return $model->rating;
                    }
                ],
            ],
        ]); ?>
    </div>
</div>