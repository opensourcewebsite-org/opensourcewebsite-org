<?php

use app\models\FormModels\CurrencyExchange\OrderPaymentMethods;
use yii\widgets\ActiveForm;
use yii\web\View;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;

/**
 * @var $this View
 * @var $model OrderPaymentMethods
 * @var $paymentsBuyTypes array
 */

$this->title = Yii::t('app', 'Update Currency Exchange Order Payment Buy Methods');
?>
<div class="modal-header">
    <h4 class="modal-title"><?= $this->title ?></h4>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
</div>
<div class="modal-body">

    <?php if (!$paymentsBuyTypes) : ?>
        <h3 class="text-center">
            <?= Yii::t('app', 'Currently there is no Payment Methods available for selected currency') ?>
        </h3>
    <?php else: ?>
        <div class="currency-exchange-order-form">

            <?php $form = ActiveForm::begin() ?>
            <div class="row">
                <div class="col-12">

                    <?= $form->field($model, 'buyingPaymentMethods')->widget(Select2::class, [
                        'theme' => Select2::THEME_DEFAULT,
                        'data' => ArrayHelper::map($paymentsBuyTypes, 'id', 'name'),
                        'options' => [
                            'placeholder' => Yii::t('app', 'Select Payment Method...'),
                            'multiple' => true,
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'closeOnSelect' => false,
                        ],
                    ])?>

                    <div class="modal-footer">
                        <?= SaveButton::widget(); ?>
                        <?= CancelButton::widget(['url' => ['view', 'id' => $model->getOrder()->id]]); ?>
                    </div>

                </div>
            </div>
            <?php ActiveForm::end() ?>
        </div>
    <?php endif; ?>
</div>
