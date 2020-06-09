<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\apiTesting\models\ApiTestRequestSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="api-test-request-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id'); ?>

    <?= $form->field($model, 'server_id'); ?>

    <?= $form->field($model, 'name'); ?>

    <?= $form->field($model, 'method'); ?>

    <?= $form->field($model, 'uri'); ?>

    <?php // echo $form->field($model, 'body')?>

    <?php // echo $form->field($model, 'correct_response_code')?>

    <?php // echo $form->field($model, 'updated_at')?>

    <?php // echo $form->field($model, 'updated_by')?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']); ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']); ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
