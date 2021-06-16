<?php

use app\models\Currency;
use app\models\CurrencyExchangeOrder;
use app\models\PaymentMethod;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\SaveButton;
use app\widgets\LocationPickerWidget\LocationPickerWidget;
use dosamigos\leaflet\layers\Marker;
use dosamigos\leaflet\layers\TileLayer;
use dosamigos\leaflet\types\LatLng;
use dosamigos\leaflet\widgets\Map;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use dosamigos\leaflet\LeafLet;
use app\assets\LeafletLocateControlAsset;

/* @var $this yii\web\View */
/* @var $model CurrencyExchangeOrder */
/* @var $form yii\widgets\ActiveForm */
/* @var $cashPaymentMethod PaymentMethod */

LeafletLocateControlAsset::register($this);

$labelOptional = ' (' . Yii::t('app', 'optional') . ')';
?>
    <div class="currency-exchange-order-form">
        <?php $form = ActiveForm::begin(); ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php if ($model->isNewRecord): ?>
                            <?= $this->render('__sell_buy_currency_fields', [
                                    'form' => $form,
                                    'model' => $model,
                            ]) ?>
                        <?php else: ?>
                            <div class="row">
                                <div class="col d-flex">
                                    <p><?= $model->getAttributeLabel('selling_currency_id') ?>:</p>&nbsp;
                                    <strong><?= $model->sellingCurrency->code ?> - <?= $model->sellingCurrency->name ?></strong>
                                </div>
                                <div class="col d-flex">
                                    <p><?= $model->getAttributeLabel('buying_currency_id') ?>:</p>&nbsp;
                                    <strong><?= $model->buyingCurrency->code ?> - <?= $model->buyingCurrency->name ?></strong>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="row">
                            <div class="col">
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="CurrencyExchangeOrder[cross_rate_on]" value="0"/>
                                    <input type="checkbox"
                                           name="CurrencyExchangeOrder[cross_rate_on]"
                                        <?= $model->cross_rate_on ? 'checked' : '' ?>
                                           value="1"
                                           class="custom-control-input"
                                           id="crossRateCheckbox"
                                    >
                                    <label class="custom-control-label" for="crossRateCheckbox"><?= $model->getAttributeLabel('cross_rate_on') ?></label>
                                </div>
                            </div>
                        </div>
                        <div class="sell-buy-rates-div" <?= !$model->cross_rate_on ?: 'style="display: none;"' ?>>
                            <div class="row">
                                <div class="col">
                                    <?= $form->field($model, 'selling_rate')
                                        ->textInput([
                                            'maxlength' => true,
                                            'placeholder' => '∞',
                                        ])
                                        ->label($model->getAttributeLabel('selling_rate') . $labelOptional); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <?= $form->field($model, 'buying_rate')
                                        ->textInput([
                                            'id' => 'buying_rate',
                                            'maxlength' => true,
                                            'placeholder' => '∞',
                                        ])
                                        ->label($model->getAttributeLabel('buying_rate') . $labelOptional); ?>
                                </div>
                            </div>
                        </div>
                        <hr/>
                        <div class="row">
                            <div class="col">
                                <?= $form->field($model, 'selling_currency_min_amount')
                                    ->textInput([
                                        'maxlength' => true,
                                        'placeholder' => '∞',
                                    ])
                                    ->label($model->getAttributeLabel('selling_currency_min_amount') . $labelOptional); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <?= $form->field($model, 'selling_currency_max_amount')
                                    ->textInput([
                                        'maxlength' => true,
                                        'placeholder' => '∞',
                                    ])
                                    ->label($model->getAttributeLabel('selling_currency_max_amount') . $labelOptional); ?>
                            </div>
                        </div>
                        <hr/>
                        <div class="row">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="CurrencyExchangeOrder[selling_cash_on]" value="0"/>
                                <input type="checkbox"
                                       name="CurrencyExchangeOrder[selling_cash_on]"
                                    <?= $model->selling_cash_on ? 'checked' : '' ?>
                                       value="1"
                                       class="custom-control-input allowCacheCheckbox"
                                       id="cashSellCheckbox"
                                >
                                <label class="custom-control-label" for="cashSellCheckbox"><?= Yii::t('app', 'Selling cash') ?></label>
                            </div>
                        </div>
                        <div class="selling-location-radius-div">
                            <div class="row">
                                <div class="col">
                                    <?= $form->field($model, 'selling_location')->widget(LocationPickerWidget::class) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <?= $form->field($model, 'selling_delivery_radius')
                                        ->textInput([
                                            'maxlength' => true,
                                            'placeholder' => 0,
                                        ])
                                        ->label(Yii::t('app', 'Delivery radius') . ', km' . $labelOptional)
                                    ?>
                                </div>
                            </div>
                        </div>
                        <hr/>
                        <div class="row">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="CurrencyExchangeOrder[buying_cash_on]" value="0"/>
                                <input type="checkbox"
                                       name="CurrencyExchangeOrder[buying_cash_on]"
                                    <?= $model->buying_cash_on ? 'checked' : '' ?>
                                       value="1"
                                       class="custom-control-input allowCacheCheckbox"
                                       id="cashBuyCheckbox"
                                >
                                <label class="custom-control-label" for="cashBuyCheckbox"><?= Yii::t('app', 'Buying cash') ?></label>
                            </div>
                        </div>
                        <div class="buying-location-radius-div">
                            <div class="row">
                                <div class="col">
                                    <?= $form->field($model, 'buying_location')->widget(LocationPickerWidget::class) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <?= $form->field($model, 'buying_delivery_radius')
                                        ->textInput([
                                            'maxlength' => true,
                                            'placeholder' => 0,
                                        ])
                                        ->label(Yii::t('app', 'Delivery radius') . ', km' . $labelOptional)
                                    ?>
                                </div>
                            </div>
                        </div>

                        <?php
                        $this->registerJs(
                                        <<<JS

                        function updateVisibility() {
                            const sellingLocationDiv = $('.selling-location-radius-div');
                            const buyingLocationDiv = $('.buying-location-radius-div');

                            ($('#cashSellCheckbox').prop('checked')) ?
                                sellingLocationDiv.show() : sellingLocationDiv.hide();
                            ($('#cashBuyCheckbox').prop('checked')) ?
                                buyingLocationDiv.show() : buyingLocationDiv.hide();

                        }

                        $('.allowCacheCheckbox').on('click', function(){
                            updateVisibility();
                        })

                        updateVisibility();
                        JS
                                    );
                        ?>

                    </div>
                    <div class="card-footer">
                        <?= SaveButton::widget(); ?>
                        <?= CancelButton::widget(['url' => '/currency-exchange-order']); ?>
                        <?php if ((string)$model->user_id === (string)Yii::$app->user->id) : ?>
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

$locationMapActionUrl = Url::to(['/currency-exchange-order/location-map-modal']);

$jsMessages = [
    'delete-confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
    'delete-error' => Yii::t('app', 'Sorry, there was an error while trying to delete this item') . '.',
];

$this->registerJs(
    <<<JS

$('#crossRateCheckbox').on('change', function(){
    if (!$(this).prop('checked')) {
        $('.sell-buy-rates-div').show();
    } else {
        $('.sell-buy-rates-div').hide();
    }
});

const calculateCrossRate = (rate) => {
    const curVal = parseFloat(rate);
    if (!isNaN(curVal) && curVal !== 0) {
        return (1/curVal).toFixed(8);
    }
    return '';
}

$('#currencyexchangeorder-selling_rate').on('change', function(){
    $('#buying_rate').val(calculateCrossRate($(this).val()));
});

$('#buying_rate').on('change', function(){
    $('#currencyexchangeorder-selling_rate').val(calculateCrossRate($(this).val()));
});

$("#delete-currency-exchange-order").on("click", function(event) {
    event.preventDefault();
    var url = $(this).attr("href");

    if (confirm("{$jsMessages['delete-confirm']}")) {
        $.post(url, {}, function(result) {
            if (result === "1") {
                document.location.href = "$urlRedirect";
            } else {
                alert("{$jsMessages['delete-error']}");
            }
        });
    }

    return false;
});
JS
);
