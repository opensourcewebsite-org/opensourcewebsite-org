<?php

use yii\widgets\ActiveForm;
use yii\web\View;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use app\widgets\selects\PaymentMethodSelect;

/**
 * @var $this View
 * @var $model CurrencyExchangeOrder
 */

$form = ActiveForm::begin([
 'enableAjaxValidation' => true,
]);
?>
<div class="form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?php $model->buyingPaymentMethodIds = $model->getBuyingPaymentMethodIds() ?>
                            <?= $form->field($model, 'buyingPaymentMethodIds')->widget(PaymentMethodSelect::class, [
                                'currencyId' => $model->buyingCurrency->id,
                            ])
                            ->label(false); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <?= SaveButton::widget(); ?>
                <?= CancelButton::widget(); ?>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end() ?>
