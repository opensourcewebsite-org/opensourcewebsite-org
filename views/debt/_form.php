<?php

use app\models\Debt;
use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use janisto\timepicker\TimePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Debt */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="debt-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'user')->widget(Select2::class, [
                                'data' => ArrayHelper::map($user, 'id', 'email'),
                                'options' => [
                                    'prompt' => '',
                                ],
                            ]); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'currency_id')->widget(Select2::class, [
                                'data' => ArrayHelper::map($currency, 'id', 'code'),
                                'options' => [
                                    'prompt' => '',
                                ],
                            ]); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'amount')->textInput(); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'direction')->dropDownList([
                                Debt::DIRECTION_DEPOSIT => 'Deposit',
                                Debt::DIRECTION_CREDIT => 'Credit',
                            ]); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'valid_from_date')->widget(DatePicker::classname(), [
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format' => 'mm/dd/yyyy',
                                ],
                            ]); ?>
                        </div>
                        <div class="col">
                            <?= $form->field($model, 'valid_from_time')->widget(TimePicker::classname(), [
                                'mode' => 'time',
                            ]); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']); ?>
                    <?= Html::a(Yii::t('app', 'Cancel'), ['/debt'], [
                        'class' => 'btn btn-secondary',
                        'title' => Yii::t('app', 'Cancel'),
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>