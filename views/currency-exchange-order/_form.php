<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\CurrencyExchangeOrder */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="currency-exchange-order-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'user_id')->textInput() ?>

    <?= $form->field($model, 'selling_currency_id')->textInput() ?>

    <?= $form->field($model, 'buying_currency_id')->textInput() ?>

    <?= $form->field($model, 'selling_rate')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'buying_rate')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'selling_currency_min_amount')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'selling_currency_max_amount')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'renewed_at')->textInput() ?>

    <?= $form->field($model, 'delivery_radius')->textInput() ?>

    <?= $form->field($model, 'location_lat')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'location_lon')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'processed_at')->textInput() ?>

    <?= $form->field($model, 'selling_cash_on')->textInput() ?>

    <?= $form->field($model, 'buying_cash_on')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
