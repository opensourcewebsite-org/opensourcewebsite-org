<?php

use app\models\Debt;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
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
                                'data' => ArrayHelper::map($user, 'id', 'displayName'),
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
                            <?= $form->field($model, 'amount', [
									'inputOptions' => [
										'autocomplete' => 'off'
									]
								])->textInput(); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'direction')->dropDownList([
                                Debt::DIRECTION_DEPOSIT => 'My Deposit',
                                Debt::DIRECTION_CREDIT => 'My Credit',
                            ]); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?= CancelButton::widget([
                        'url' => ['/debt']
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
