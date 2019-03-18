<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\models\UserWikiToken;
use app\models\UserWikiPage;
use app\components\TitleColumn;
/* @var $this \yii\web\View */

$this->title = Yii::t('menu', 'WikiNews Pages');


?>
<div class="card">
    <div class="card-header d-flex p-0">
        <ul class="nav nav-pills ml-auto p-2">
            <li class="nav-item align-self-center mr-4">
                <?= Html::button('<i class="fa fa-plus"></i>', [
                    'class' => 'btn btn-outline-success',
                    'title' => 'Add Wikipedia domains that you use',
                    'onclick' => '$.get("' . Yii::$app->urlManager->createUrl(['wikinews-pages/create']) . '", {}, function (result){
                    $("#main-modal-body").html(result);
                    $("#main-modal-header").html("' . Yii::t('app', 'Add Wikinews page') . '").data("target", "' . Yii::$app->urlManager->createUrl(['wikinews-pages/create']) . '");
                    $("#main-modal").modal("show");
                })',
                ]); ?>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
       
       <?=GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'filterPosition' => false,
						
                        'summary' => false,
                        'layout' => "{items}</div></div><div class='card-footer clearfix'>{pager}</div>",
                        'tableOptions' => ['class' => 'table table-condensed table-hover'],
                        'pager' => [
                            'hideOnSinglePage' => false,
							
                            // Customzing options for pager container tag
                            'options' => [
                                'tag' => 'ul',
                                'class' => 'pagination float-right',
                            ],
                    
                            // Customzing CSS class for pager link
                            'linkOptions' => ['class' => 'page-link'],
                            'linkContainerOptions' => ['class' => 'page-item'],
                            'activePageCssClass' => 'active',
                            'disabledPageCssClass' => 'disabled',
                            'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link disabled'],
                        ],
                        'columns' => [
						    [
                                'attribute' => 'language_id',
                                'contentOptions' => ['style' => 'width: 45%; white-space: normal'],
                                'value' => function ($model) {
                                    return $model->language->name;
                                },
                                'format' => 'html',
                            ],
                            [
                                'attribute' => 'title',
                                'contentOptions' => ['style' => 'width: 45%; white-space: normal'],
                                'value' => function ($model) {
                                    return Html::a($model->title, ['/issue/view', 'id' => $model->id]);
                                },
                                'format' => 'html',
                            ],
                            [
                                'attribute' => 'created_at',
                                'contentOptions' => ['style' => 'width: 12%; white-space: normal'],
                                'format' =>  ['date', 'php:M d Y'],
                            ],
                            
                        ],
                    ]);
        ?>
    </div>
</div>