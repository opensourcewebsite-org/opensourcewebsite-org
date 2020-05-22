<?php

use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\EditButton;
use app\widgets\buttons\SaveButton;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroupCommand */

?>

<div class="card-header">
    <div class="row">
        <div class="col-11">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="col-1 text-right">
            <?= EditButton::widget([
                'url' => '#',
                'options' => [
                    'data-toggle' => 'modal',
                    'data-target' => '#exampleModalLongEditCommand',
                ]
            ]); ?>
            <?php $form = ActiveForm::begin() ?>
            <div class="modal fade" id="exampleModalLongEditCommand" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLongTitle">Edit command: <?= $model->command ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-left">
                            <?= $form->field($model, 'command')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'is_default')->checkbox([
                                'value'   => 1,
                                //'checked' => $model->is_default,
                            ]) ?>
                        </div>
                        <div class="card-footer text-left">
                            <?= SaveButton::widget(); ?>
                            <?= CancelButton::widget(); ?>
                            <?= DeleteButton::widget([
                                'url' => ['command-delete', 'id' => $model->id]
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
