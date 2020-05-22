<?php

use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\SaveButton;
use yii\bootstrap4\ActiveForm;

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
                <?= SaveButton::widget(); ?>
                <?= CancelButton::widget(); ?>
                <?= DeleteButton::widget([
                    'url' => ['bots-delete', 'id' => $model->id]
                ]); ?>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
