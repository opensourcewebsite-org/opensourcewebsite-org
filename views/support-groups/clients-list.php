<?php

use yii\grid\GridView;
use yii\helpers\Html;
use app\models\SupportGroupOutsideMessage;

/* @var $this \yii\web\View */
/* @var $searchModel app\models\search\SupportGroupBotClientSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Clients List';
$this->params['breadcrumbs'][] = ['label' => 'Support Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Languages', 'url' => ['clients-languages', 'id' => $searchModel->support_group_id]];
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
                            $model->showUserName(),
                            [
                                'clients-view',
                                'id' => $model->id,
                                'page' => SupportGroupOutsideMessage::getLastPage($model->id)
                            ]
                        );
                    },
                ],
                [
                    'attribute' => 'supportGroupBot.title',
                    'label' => 'Bot',
                ],
                [
                    'attribute' => 'last_message_at',
                    'format' => 'relativeTime',
                    'label' => 'Last update',
                ],
            ],
        ]); ?>
    </div>
</div>
