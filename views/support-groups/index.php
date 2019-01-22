<?php

use yii\bootstrap\ButtonDropdown;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Support Groups';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="col-md-12">
    <?php $this->beginBlock('content-header-data'); ?>
        <div class="row mb-2">
            <div class="col-sm-4">
                <h1 class="text-dark"><?= Html::encode($this->title) ?></h1>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col">
                <div class="alert alert-info" role="alert">
                    <b>Support Groups:</b> <?= Yii::$app->user->identity->supportGroupCount ?>/<?= Yii::$app->user->identity->maxSupportGroup ?>. 
                    (<?= $settingQty ?> per 1 User Rating)
                </div>
            </div>
        </div>
    <?php $this->endBlock(); ?>
    <div class="card">
        <div class="card-header text-right">
            <?= Html::a('New Support Group', ['create'], ['class' => 'btn btn-success ml-3']) ?>
        </div>
        <div class="card-body p-0">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'summary' => false,
                'showHeader' => false,
                'tableOptions' => ['class' => 'table table-hover table-condensed'],
                'columns' => [
                    'title',
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{all}',
                        'buttons' => [
                            'all' => function ($url, $model, $key) {
                                if($model->user_id == Yii::$app->user->identity->id) {
                                    return ButtonDropdown::widget([
                                        'encodeLabel' => false, // if you're going to use html on the button label
                                        'label' => '<i class="fas fa-cog"></i>',
                                        'dropdown' => [
                                            //'encodeLabels' => false, // if you're going to use html on the items' labels
                                            'items' => [
                                                [
                                                    'label' => Yii::t('app', 'Members'),
                                                    'url' => ['members', 'id' => $key],
                                                    'linkOptions' => ['class' => 'dropdown-item']
                                                ],
                                                [
                                                    'label' => Yii::t('app', 'Telegram bots'),
                                                    'url' => ['bots', 'id' => $key],
                                                    'linkOptions' => ['class' => 'dropdown-item']
                                                ],
                                                /*[
                                                    'label' => Yii::t('app', 'Commands'),
                                                    'url' => ['commands', 'id' => $key],
                                                    'linkOptions' => ['class' => 'dropdown-item']
                                                ],*/
                                                [
                                                    'label' => Yii::t('app', 'Edit'),
                                                    'url' => ['update', 'id' => $key],
                                                    'linkOptions' => ['class' => 'dropdown-item']
                                                ],
                                            ],
                                        ],
                                        'options' => [
                                            'class' => 'btn-default',   // btn-success, btn-info, et cetera
                                        ],
                                    ]);
                                }
                            },
                        ],
                    ],
                    [
                        'content' => function ($model) {
                            if($model->user_id == Yii::$app->user->identity->id || in_array(Yii::$app->user->identity->id, ArrayHelper::getColumn($model->supportGroupMembers, 'user_id'))) {
                                return Html::a('commands', ['commands', 'id' => $model->id]);
                            }
                        }
                    ],
                    [
                        'content' => function ($model) {
                            if($model->user_id == Yii::$app->user->identity->id || in_array(Yii::$app->user->identity->id, ArrayHelper::getColumn($model->supportGroupMembers, 'user_id'))) {
                                return Html::a('clients', ['clients-languages', 'id' => $model->id]);
                            }
                        }
                    ],
                ],
            ]); ?>
            <div class="card-footer clearfix">
            </div>
        </div>
    </div>
</div>
