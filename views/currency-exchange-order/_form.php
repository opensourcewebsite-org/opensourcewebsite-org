<?php

use app\models\CurrencyExchangeOrder;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model CurrencyExchangeOrder */
/* @var $form yii\widgets\ActiveForm */

$labelOptional = ' (' . Yii::t('app', 'optional') . ')';
?>
<div class="currency-exchange-order-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'selling_currency_id')->widget(Select2::class, [
                                'data' => ArrayHelper::map($currency, 'id', 'code'),
                                'options' => [
                                    'prompt' => '',
                                ],
                            ]); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'buying_currency_id')->widget(Select2::class, [
                                'data' => ArrayHelper::map($currency, 'id', 'code'),
                                'options' => [
                                    'prompt' => '',
                                ],
                            ]); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'selling_rate')
                                ->textInput(['maxlength' => true])
                                ->label($model->getAttributeLabel('selling_rate')); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'selling_currency_min_amount')
                                ->textInput(['maxlength' => true])
                                ->label($model->getAttributeLabel('selling_currency_min_amount') . $labelOptional); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'selling_currency_max_amount')
                                ->textInput(['maxlength' => true])
                                ->label($model->getAttributeLabel('selling_currency_max_amount') . $labelOptional); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?= CancelButton::widget(['url' => '/currency-exchange-order']); ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
