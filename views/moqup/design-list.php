<?php
/* @var $this \yii\web\View */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = Yii::t('menu', 'Moqups');
?>
<?php Pjax::begin(); ?>
<div class="card">
    <div class="card-header d-flex p-0">
        <h3 class="card-title p-3">
            Pages
        </h3>
        <ul class="nav nav-pills ml-auto p-2">
            <li class="nav-item align-self-center mr-4">
                <a href="<?= Yii::$app->urlManager->createUrl(['moqup/design-edit']) ?>">
                    <button type="button" class="btn btn-outline-success" data-toggle="tooltip" data-placement="top" title="Create New">
                        <i class="fa fa-plus"></i>
                    </button>
                </a>
            </li>
            <li class="nav-item">
                <?= Html::a(Yii::t('moqup', 'All') . ' <span class="badge badge-light ml-1">' . $countAll . '</span>', 
                    ['moqup/design-list'], [
                        'class' => 'nav-link show ' . ($viewYours != 1 ? 'active' : ''),
                    ]); ?>
            </li>
            <li class="nav-item">
                <?= Html::a(Yii::t('moqup', 'Yours') . ' <span class="badge badge-light ml-1">' . $countYours . '</span>', 
                    ['moqup/design-list', 'viewYours' => 1], [
                        'class' => 'nav-link ' . ($viewYours == 1 ? ' active' : ''),
                    ]); ?>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'filterPosition' => false,
            'summary' => false,
            'tableOptions' => ['class' => 'table table-hover'],
            'columns' => [
                [
                    'attribute' => 'title',
                    'contentOptions' => ['style' => 'width: ' . (($viewYours) ? '45%' : '25%') . '; white-space: normal']
                ],
                [
                    'attribute' => 'user_id',
                    'contentOptions' => ['style' => 'width: 20%; white-space: normal'],
                    'value' => function($model){
                        return $model->user->username;
                    },
                    'visible' => $viewYours == false,
                ],
                [
                    'attribute' => 'created_at',
                    'contentOptions' => ['style' => 'width: 20%; white-space: normal'],
                    'format' => ['date', 'php:Y-m-d h:i a']
                ],
                [
                    'attribute' => 'updated_at',
                    'contentOptions' => ['style' => 'width: 20%; white-space: normal'],
                    'format' => ['date', 'php:Y-m-d h:i a']
                ],
                
                [
                    'class' => 'yii\grid\ActionColumn',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            $content = '<i class="fas fa-external-link-alt"></i>';
                            return Html::a($content, ['moqup/design-view/', 'id' => $model->id], [
                                'data-pjax' => 0,
                                'target' => '_blank',
                                'class' => 'btn btn-sm btn-outline-primary',
                                'title' => 'View',
                            ]);
                        },
                        'update' => function ($url, $model, $key) {
                            $content = '<i class="fas fa-edit"></i>';
                            return Html::a($content, ['moqup/design-edit/', 'id' => $model->id], [
                                'data-pjax' => 0,
                                'class' => 'btn btn-sm btn-outline-secondary',
                                'title' => 'Edit',
                            ]);
                        },
                        'delete' => function ($url, $model, $key) {
                            $content = '<i class="fas fa-trash-alt"></i>';
                            return Html::a($content, ['moqup/design-delete/', 'id' => $model->id], [
                                'data-pjax' => 0,
                                'data-method' => 'post',
                                'class' => 'delete-moqup-anchor btn btn-sm btn-outline-danger',
                                'title' => 'Delete',
                            ]);
                        }
                    ],
                    'visibleButtons' => [
                        'update' => $viewYours,
                        'delete' => $viewYours,
                    ],
                ],
            ],
        ]); ?>
    </div>
</div>


<?php
$this->registerJs('$(".delete-moqup-anchor").on("click", function(event) {
    event.preventDefault();
    var url = $(this).attr("href");

    if (confirm("' . Yii::t('moqup', 'Are you sure you want to delete this moqup?') . '")) {
        $.post(url, {}, function(result) {
            if (result == "1") {
                location.reload();
            }
            else {
                alert("' . Yii::t('moqup', 'Sorry, there was an error while trying to delete the moqup') . '");
            }
        });
    } 

    return false;
});');

Pjax::end();