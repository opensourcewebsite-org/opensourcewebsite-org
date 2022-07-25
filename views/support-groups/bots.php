<?php

use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use yii\bootstrap4\ActiveForm;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroup */
/* @var $bot app\models\SupportGroupBot */
/* @var $dataProvider \yii\data\ActiveDataProvider */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Support Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="col-md-12">
    <?php $this->beginBlock('content-header-data'); ?>
        <div class="row mb-2">
            <div class="col-sm-4">
                <h1 class="text-dark mt-4"><?= Html::encode($this->title) ?></h1>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col">
                <div class="alert alert-info" role="alert">
                    <b>Support Group Bots:</b> <?= Yii::$app->user->identity->botsCount ?>/<?= Yii::$app->user->identity->maxBots ?>.
                    (<?= $settingQty ?> per 1 User Rating)
                </div>
            </div>
        </div>
    <?php $this->endBlock(); ?>
    <div class="card">
        <div class="card-header text-right">
            <?php $form = ActiveForm::begin(['enableAjaxValidation' => true]) ?>
            <a class="btn btn-success ml-3" href="#" title="New bot" data-toggle="modal" data-target="#exampleModalLong">New bot</a>
            <div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-body text-left">
                            <?php echo $form->field($bot, 'title')->textInput(['maxlength' => true]) ?>
                            <?php echo $form->field($bot, 'token')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="card-footer text-left">
                            <?= SaveButton::widget(); ?>
                            <?= CancelButton::widget(); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'summary' => false,
            'tableOptions' => ['class' => 'table table-hover table-condensed'],
            'options' => ['class' => 'card-body p-0'],
            'columns' => [
                'title',
                [
                    'class' => 'yii\grid\ActionColumn',
                    //'contentOptions' => ['class' => 'text-right'],
                    'template' => '{update}',
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            $url = Url::to(['bots-update', 'id' => $model->id]);

                            return Html::a('<i class="fas fa-edit"></i>', '#', [
                                    'class' => 'btn btn-light',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#exampleModalLong_bot_edit' . $model->id
                                ]) . $this->render('_modal', compact('model'));
                        },
                    ],
                ],
            ]
        ]); ?>
        <div class="card-footer clearfix">
        </div>
    </div>
</div>
