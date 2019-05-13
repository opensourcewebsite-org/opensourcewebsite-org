<?php

use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroupBot */
/* @var $form yii\widgets\ActiveForm */

?>
<?php $form = ActiveForm::begin(['action' => 'bots-update?id=' . $model->id , 'enableAjaxValidation' => true]) ?>
<div class="modal fade" id="exampleModalLong_bot_edit<?= $model->id ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body text-left">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'token')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success">Save</button>
                <a class="btn btn-secondary" href="#" data-dismiss="modal">Cancel</a>
                <a class="btn btn-danger float-right" href="bots-delete?id=<?= $model->id ?>" onclick="#">Delete</a>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>