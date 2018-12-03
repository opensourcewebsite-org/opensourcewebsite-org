<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroup */
/* @var $form yii\widgets\ActiveForm */

?>
<div class="col-12">
    <div class="card">
        <div class="card-body p-0">
            <?php $form = ActiveForm::begin(); ?>
                <div class="col-3 p-3">
                    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

                    <p>Languages</p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="option1" checked disabled>
                        <label class="form-check-label">English</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="option1">
                        <label class="form-check-label">Russian</label>
                    </div>
                </div>
                <div class="card-footer">
                    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                    <a class="btn btn-secondary" href="/support-groups">Cancel</a>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
