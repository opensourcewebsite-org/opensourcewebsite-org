<?php

use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $searchModel app\models\search\CronJobSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Clients List';
$this->params['breadcrumbs'][] = ['label' => 'Support Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="col-md-12">
    <div class="card">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'summary'      => false,
            'tableOptions' => ['class' => 'table table-hover'],
            'pager'        => [
                'options'                       => [
                    'class' => 'pagination ml-4',
                ],
                'linkContainerOptions'          => [
                    'class' => 'page-item',
                ],
                'linkOptions'                   => [
                    'class' => 'page-link',
                ],
                'disabledListItemSubTagOptions' => [
                    'tag' => 'a', 'class' => 'page-link',
                ],
            ],
            'columns'      => [
                [
                    'attribute' => 'provider_bot_user_name',
                    'format'    => 'raw',
                    'label'     => 'Name',
                    'enableSorting' => false,
                    'content'   => function ($model) {
                        return Html::a(
                            ((!empty($model->provider_bot_user_name)) ?
                                $model->provider_bot_user_name . ' ' : '') .
                            $model->provider_bot_user_id,
                            ['clients-view', 'id' => $model->id]
                        );
                    },
                ],
                'supportGroupBot.title',
                [
                    'attribute' => 'last_message_at',
                    'format' => 'relativeTime',
                ],
            ],
        ]); ?>
    </div>
</div>
