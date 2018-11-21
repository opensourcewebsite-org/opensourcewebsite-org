<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\components\Converter;
use app\components\helpers\IssuesHelper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\IssueSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Issues');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="issue-index">
    <div class="row">
        <div class="col-12">
            <?php if ($viewYours): ?>
                <?php $this->beginBlock('content-header-data'); ?>
                    <div class="row mb-2">
                        <div class="col-sm-4">
                            <h1 class="text-dark mt-4"><?= Html::encode($this->title) ?></h1>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col">
                            <div class="alert alert-info" role="alert">
                                <b>Issues:</b> <?= Yii::$app->user->identity->moqupsCount ?>/<?= Yii::$app->user->identity->maxIssuesNumber ?>. 
                                (<?= $maxIssueValue ?> per 1 User Rating)
                            </div>
                        </div>
                    </div>
                <?php $this->endBlock(); ?>
            <?php endif; ?>
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-6">
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                <?= Html::a(Yii::t('app', 'New') . ' <span class="badge badge-light ml-1">' . $countNew . '</span>', ['/issue', 'viewNew' => 1], [
                                    'class' => 'nav-link ' . (isset($params['viewNew']) && $params['viewNew'] == 1 ? ' active' : ''),
                                ]); ?>
                                </li>
                                <li class="nav-item">
                                <?= Html::a(Yii::t('app', 'Yes'), ['/issue', 'viewYes' => 1], [
                                    'class' => 'nav-link ' . (isset($params['viewYes']) && $params['viewYes'] == 1 ? ' active' : ''),
                                ]); ?>
                                </li>
                                <li class="nav-item">
                                <?= Html::a(Yii::t('app', 'Neutral'), ['/issue', 'viewNeutral' => 1], [
                                    'class' => 'nav-link ' . (isset($params['viewNeutral']) && $params['viewNeutral'] == 1 ? ' active' : ''),
                                ]); ?>
                                </li>
                                <li class="nav-item">
                                <?= Html::a(Yii::t('app', 'No'), ['/issue', 'viewNo' => 1], [
                                    'class' => 'nav-link ' . (isset($params['viewNo']) && $params['viewNo'] == 1 ? ' active' : ''),
                                ]); ?>
                                </li>
                                <li class="nav-item">
                                <?= Html::a(Yii::t('app', 'Yours') . ' <span class="badge badge-light ml-1">' . $countYours . '</span>', ['/issue', 'viewYours' => 1], [
                                    'class' => 'nav-link ' . (isset($params['viewYours']) && $params['viewYours'] == 1 ? ' active' : ''),
                                ]); ?>
                                </li>
                            </ul>
                        </div>
                        <div class="col-sm-6">
                            <form class="input-group">
                                <input type="text" name="table_search" class="form-control pull-right" placeholder="Search">
                                <div class="input-group-btn">
                                    <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                                </div>
                                <?= Html::a(Yii::t('app', 'New Issue'), ['issue/create'], [
                                    'class' => 'btn btn-success ml-3',
                                    'title' => Yii::t('app', 'New Issue'),
                                ]); ?>
                                
                            </form>
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
                            [
                                'attribute' => 'id',
                                'contentOptions' => ['style' => 'width: 5%; white-space: normal'],
                            ],
                            [
                                'attribute' => 'title',
                                'contentOptions' => ['style' => 'width: 45%; white-space: normal'],
                            ],
                            [
                                'attribute' => 'created_at',
                                'contentOptions' => ['style' => 'width: 12%; white-space: normal'],
                                'format' => 'relativeTime',
                            ],
                            [
                                'attribute' => 'votes',
                                'contentOptions' => ['style' => 'width: 26%; white-space: normal'],
                                'format' => 'html',
                                'value' => function ($model) {
                                    return IssuesHelper::getVoteHTMl($model);
                                },
                            ],
                    
                            ['class' => 'yii\grid\ActionColumn',
                                'contentOptions' => ['style' => 'width: 12%; white-space: normal'],
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        $content = '<i class="fas fa-external-link-alt"></i>';
                                        return Html::a($content, ['issue/view/', 'id' => $model->id], [
                                            'data-pjax' => 0,
                                            'class' => 'btn btn-sm btn-outline-primary',
                                            'title' => 'View',
                                        ]);
                                    },
                                ],
                            ],
                        ],
                    ]);
                ?>
                    
            </div>
        </div> 
    </div>
</div>


<?php
$this->registerJs('$(".delete-issue-anchor").on("click", function(event) {
    event.preventDefault();
    var url = $(this).attr("href");

    if (confirm("' . Yii::t('app', 'Are you sure you want to delete this issue?') . '")) {
        $.post(url, {}, function(result) {
            if (result == "1") {
                location.reload();
            }
            else {
                alert("' . Yii::t('app', 'Sorry, there was an error while trying to delete the issue.') . '");
            }
        });
    }

    return false;
});');
