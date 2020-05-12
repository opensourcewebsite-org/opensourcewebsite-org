<?php

use yii\bootstrap4\ActiveForm;
use app\widgets\buttons\AddButton;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use yii\bootstrap\ActiveForm;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroup */
/* @var $command app\models\SupportGroupCommand */
/* @var $dataProvider \yii\data\ActiveDataProvider */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Support Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="col-md-12">
    <div class="card">
        <div class="card-header text-right">
            <?php $form = ActiveForm::begin(['enableAjaxValidation' => true]) ?>
            <?= AddButton::widget([
                'url' => '#',
                'text' => 'New command',
                'options' => [
                    'title' => 'New command',
                    'data-toggle' => 'modal',
                    'data-target' => '#exampleModalLong'
                ]
            ]); ?>
            <div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLongTitle"><?= Yii::t('app', 'Add command'); ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-left">
                            <?= $form->field($command, 'command')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($command, 'is_default')->checkbox() ?>
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
            'showHeader' => false,
            'tableOptions' => ['class' => 'table table-hover table-condensed'],
            'options' => ['class' => 'card-body p-0'],
            'columns' => [
                [
                    'attribute' => 'command',
                    'content' => function ($model) {
                        return Html::a($model->command, 'view-command?id=' . $model->id);
                    }
                ],
                [
                    'attribute' => 'is_default',
                    'content' => function ($model) {
                        return $model->is_default ? 'default' : '';
                    }
                ],
            ]
        ]); ?>
        <div class="card-footer clearfix">
        </div>
    </div>
</div>
