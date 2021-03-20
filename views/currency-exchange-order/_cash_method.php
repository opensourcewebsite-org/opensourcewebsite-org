<?php

use app\models\CurrencyExchangeOrder;
use app\models\PaymentMethod;
use yii\web\View;
use yii\widgets\ActiveForm;

/**
 * @var $this View
 * @var $form ActiveForm
 * @var $model CurrencyExchangeOrder
 */

?>

    <div class="row">
        <div class="col">
            <div class="custom-control custom-switch">
                <input type="hidden" name="CurrencyExchangeOrder[selling_cash_on]" value="0"/>
                <input type="checkbox"
                       name="CurrencyExchangeOrder[selling_cash_on]"
                    <?= $model->selling_cash_on ? 'checked' : '' ?>
                       value="1"
                       class="custom-control-input allowCacheCheckbox"
                       id="cashSellCheckbox">

                <label class="custom-control-label" for="cashSellCheckbox">Cash Sell</label>
            </div>
            <div class="custom-control custom-switch">
                <input type="hidden" name="CurrencyExchangeOrder[buying_cash_on]" value="0"/>
                <input type="checkbox"
                       name="CurrencyExchangeOrder[buying_cash_on]"
                    <?= $model->buying_cash_on ? 'checked' : '' ?>
                       value="1"
                       class="custom-control-input allowCacheCheckbox"
                       id="cashBuyCheckbox">

                <label class="custom-control-label" for="cashBuyCheckbox">Cash Buy</label>
            </div>
        </div>
    </div>

<?php
$this->registerJs(<<<JS

    function updateVisibility() {
        ($('#cashSellCheckbox').prop('checked') ) ?
            $('.selling-location-radius-div').show() : $('.selling-location-radius-div').hide();
        ($('#cashBuyCheckbox').prop('checked') ) ?
            $('.buying-location-radius-div').show() : $('.buying-location-radius-div').hide();

    }

    $('.allowCacheCheckbox').on('click', function(){
        updateVisibility();
    })

    updateVisibility();
JS
);
