<?php
/* @var $this \yii\web\View */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\components\Converter;

$this->title = Yii::t('menu', 'Moqups');
?>
<?php if ($viewYours): ?>
    <?php $this->beginBlock('content-header-data'); ?>
        <div class="row mb-2">
            <div class="col-sm-4">
                <h1 class="text-dark mt-4"><?= Html::encode($this->title) ?></h1>
            </div>
        </div>
        <div class="row mb-2">
            <div class="alert alert-info" role="alert">
                <b>Moqups:</b> <?= Yii::$app->user->identity->moqupsCount ?>/<?= Yii::$app->user->identity->maxMoqupsNumber ?>. 
                (<?= $maxMoqupValue ?> per 1 User Rating), 
                <b>Volume:</b> <?= Converter::formatNumber(Yii::$app->user->identity->totalMoqupsSize) ?> MB/<?= Converter::formatNumber(Yii::$app->user->identity->maxMoqupsSize) ?> MB. 
                (<?= Converter::formatNumber($sizeMoqupValue) ?> MB per 1 User Rating)
            </div>
        </div>
    <?php $this->endBlock(); ?>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex p-0">
        <h3 class="card-title p-3">
            Pages
        </h3>
        <ul class="nav nav-pills ml-auto p-2">
            <li class="nav-item align-self-center mr-4">
                <?= Html::a('<i class="fa fa-plus"></i>',
                    ['moqup/design-edit'], [
                        'class' => 'btn btn-outline-success',
                        'title' => Yii::t('moqup', 'Create New'),
                    ]); ?>
            </li>
            <li class="nav-item">
                <?= Html::a(Yii::t('moqup', 'All') . ' <span class="badge badge-light ml-1">' . $countAll . '</span>',
                    ['moqup/design-list'], [
                        'class' => 'nav-link show ' . ($viewYours != 1 && !$viewFollowing ? 'active' : ''),
                    ]); ?>
            </li>
            <li class="nav-item">
                <?= Html::a(Html::tag('i', '', ['class' => 'fa fa-star'])
                    . ' <span class="badge badge-light ml-1">' . $countFollowing . '</span>',
                    ['moqup/design-list', 'viewFollowing' => 1], [
                        'class' => 'nav-link show ' . ($viewFollowing == 1 ? 'active' : ''),
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
                    'contentOptions' => ['style' => 'width: ' . (($viewYours) ? '65%' : '35%') . '; white-space: normal'],
                    'format' => 'html',
                    'value' => function($model) use ($viewYours, $viewFollowing){
                        $followed = in_array($model->id, Yii::$app->user->identity->followedMoqupsId);
                        $response = $model->title;

                        return $response;
                    },
                ],
                [
                    'attribute' => 'user_id',
                    'contentOptions' => ['style' => 'width: 40%; white-space: normal'],
                    'format' => 'html',
                    'value' => function($model) use ($viewFollowing) {
                        $response = $model->user->email;
                        
                        return $response;
                    },
                    'visible' => $viewYours == false,
                ],
                [
                    'attribute' => 'updated_at',
                    'contentOptions' => ['style' => 'width: 20%; white-space: normal'],
                    'label' => 'Last update',
                    'format' => 'relativeTime',
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

