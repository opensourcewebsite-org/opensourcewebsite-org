<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\components\Converter;
use app\components\helpers\IssuesHelper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\IssueSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Wikinews Pages');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="issue-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-6 ">&nbsp;</div>
                        <div class="col-sm-6">
                            <!-- <form class="input-group"> -->
                                <!-- <input type="text" name="table_search" class="form-control pull-right" placeholder="Search">
                                <div class="input-group-btn">
                                    <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                                </div> -->
                                <?= Html::button(Yii::t('app', '<i class="fa fa-plus"></i>'),[
                                    'data-toggle'=>'modal',
                                    'data-target'=>'#myModal',
                                    'class' => 'btn btn-success ml-3 float-right',
                                ]);
                                ?>
                            <!-- </form> -->
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
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
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'attribute' => 'language_id',
                                'value' => function ($model) {
                                    return $model->language->name;
                                },
                                'format' => 'html',
                            ],
                            [
                                'attribute' => 'title',
                                'value' => function ($model) {
                                    return $model->title;
                                },
                            ],
                            [
                                'attribute' => 'wikinews_page_url',
                                'contentOptions' => [
                                   'style' => [
                                       'max-width' => '600px',
                                       'white-space' => 'normal',
                                    ],
                                ],
                                'value' => function ($model) {
                                    return Html::a($model->wikinews_page_url,$model->wikinews_page_url,['target'=>'_blank']);
                                },
                                'format'=>'raw'
                            ],
                            [
                                'attribute' => 'created_at',
                                'contentOptions' => ['style' => 'width: 12%; white-space: normal'],
                                'format' => 'relativeTime',
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{view} {update} {delete} ',
                                'buttons' => [
                                    'view' => function($url, $model, $key) {     
                                        return Html::a('<i class="fa fa-eye"></i>',['view', 'id' => $model->id]);
                                    },
                                    'delete' => function($url, $model, $key) {     
                                        return Html::a('<i class="fa fa-trash"></i>',['delete', 'id' => $model->id]);
                                    }
                                ]
                            ],
                        ],
                    ]);
                ?>
                    
            </div>
        </div> 
    </div>
</div>

<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Add New Wikinews Page</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        
      </div>
      <div class="modal-body">
        <?= $this->render('_form', [
            'model' => $model,
            'language_arr' => $language_arr,
        ]) ?>
      </div>
      <!-- <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div> -->
    </div>

  </div>
</div>
