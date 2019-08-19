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
            <div class="col">
                <div class="alert alert-info" role="alert">
                    <b>Moqups:</b> <?= Yii::$app->user->identity->moqupsCount ?>/<?= Yii::$app->user->identity->maxMoqupsNumber ?>. 
                    (<?= $maxMoqupValue ?> per 1 User Rating)
                </div>
            </div>
        </div>
    <?php $this->endBlock(); ?>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex p-0">
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

                        return Html::a($response, ['/moqup/design-view', 'id' => $model->id]);
                    },
                ],
                [
                    'attribute' => 'user_id',
                    'contentOptions' => ['style' => 'width: 30%; white-space: normal'],
                    'format' => 'html',
                    'value' => function($model) use ($viewFollowing) {
                        $response = $model->user->name ?? $model->user->id;
                        
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
            ],
            'layout' => "{summary}\n{items}\n<div class='card-footer clearfix'>{pager}</div>",
            'pager' => [
                'options' => [
                    'class' => 'pagination float-right',
                ],
                'linkContainerOptions' => [
                    'class' => 'page-item',
                ],
                'linkOptions' => [
                    'class' => 'page-link',
                ],
                'maxButtonCount' => 5,
                'disabledListItemSubTagOptions' => [
                    'tag' => 'a',
                    'class' => 'page-link',
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

