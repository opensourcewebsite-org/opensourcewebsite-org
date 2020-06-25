<?php

use app\models\CurrencyExchangeOrder;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\SaveButton;
use yii\widgets\ActiveForm;

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
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'delivery_radius')
                                ->textInput(['maxlength' => true])
                                ->label($model->getAttributeLabel('delivery_radius') . $labelOptional); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'location_lat')
                                ->textInput(['maxlength' => true])
                                ->label($model->getAttributeLabel('location_lat') . $labelOptional); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'location_lon')
                                ->textInput(['maxlength' => true])
                                ->label($model->getAttributeLabel('location_lon') . $labelOptional); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'selling_cash_on')
                                ->textInput(['maxlength' => true])
                                ->label($model->getAttributeLabel('selling_cash_on')); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'buying_cash_on')
                                ->textInput(['maxlength' => true])
                                ->label($model->getAttributeLabel('buying_cash_on')); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?= CancelButton::widget(['url' => '/currency-exchange-order']); ?>
                    <?php if (!$model->isNewRecord && (string)$model->user_id === (string)Yii::$app->user->id) : ?>
                        <?= DeleteButton::widget([
                            'url' => ['currency-exchange-order/delete/', 'id' => $model->id],
                            'options' => [
                                'id' => 'delete-currency-exchange-order'
                            ]
                        ]); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php

$urlRedirect = Yii::$app->urlManager->createUrl(['/currency-exchange-order']);
$jsMessages = [
    'delete-confirm' => Yii::t('app', 'Are you sure you want to delete this order') . '?',
    'delete-error'   => Yii::t('app', 'Sorry, there was an error while trying to delete the order') . '.',
];

$this->registerJs(<<<JS
$("#delete-currency-exchange-order").on("click", function(event) {
    event.preventDefault();
    var url = $(this).attr("href");

    if (confirm("{$jsMessages['delete-confirm']}")) {
        $.post(url, {}, function(result) {
            if (result == "1") {
                location.href = "$urlRedirect";
            } else {
                alert("{$jsMessages['delete-error']}");
            }
        });
    }

    return false;
});
JS
);
