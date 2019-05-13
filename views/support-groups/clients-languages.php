<?php

use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $searchModel app\models\search\SupportGroupLanguageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Languages';
$this->params['breadcrumbs'][] = ['label' => 'Support Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>



<div class="col-md-12">
    <div class="card">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'summary'      => false,
            'showHeader' => false,
            'tableOptions' => ['class' => 'table table-hover'],
            'pager'        => [
                'options' => [
                    'class' => 'pagination ml-4',
                ],
                'linkContainerOptions' => [
                    'class' => 'page-item',
                ],
                'linkOptions' => [
                    'class' => 'page-link',
                ],
                'disabledListItemSubTagOptions' => [
                    'tag' => 'a', 'class' => 'page-link',
                ],
            ],
            'columns' => [
                [
                    'attribute' => 'languageCode.name_ascii',
                    'format' => 'raw',
                    'content' => function ($model) use ($searchModel) {
                        return Html::a(
                            $model->languageCode->name_ascii,
                            ['clients-list', 'id' => $searchModel->support_group_id, 'language' => $model->language_code]
                        );
                    }
                ],
            ],
        ]); ?>
    </div>
</div>
