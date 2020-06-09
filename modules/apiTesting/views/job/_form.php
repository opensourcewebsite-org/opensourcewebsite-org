<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\apiTesting\models\ApiTestJob */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="api-test-job-form">
    <div class="card">
        <div class="card-body">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'name')->textInput(['maxlength' => true]); ?>

            <div class="form-group">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']); ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
