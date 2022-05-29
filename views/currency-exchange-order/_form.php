<?php

use app\assets\LeafletLocateControlAsset;
use app\components\helpers\Html;
use app\models\Currency;
use app\models\CurrencyExchangeOrder;
use app\models\PaymentMethod;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\SaveButton;
use app\widgets\LocationPickerWidget\LocationPickerWidget;
use app\widgets\selects\CurrencySelect;
use dosamigos\leaflet\layers\Marker;
use dosamigos\leaflet\layers\TileLayer;
use dosamigos\leaflet\LeafLet;
use dosamigos\leaflet\types\LatLng;
use dosamigos\leaflet\widgets\Map;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model CurrencyExchangeOrder */
/* @var $form yii\widgets\ActiveForm */
/* @var $cashPaymentMethod PaymentMethod */

LeafletLocateControlAsset::register($this);

$labelOptional = ' (' . Yii::t('app', 'optional') . ')';

$form = ActiveForm::begin(['id' => 'form']);
?>
<div class="form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if ($model->isNewRecord): ?>
                        <div class="row">
                            <div class="col">
                                <?= $form->field($model, 'selling_currency_id')->widget(CurrencySelect::class); ?>
                            </div>
                        </div>
                    <?php else: ?>
                            <div class="row">
                                <div class="col d-flex">
                                    <strong><?= Yii::t('app', 'Sell') ?></strong>: <?= $model->sellingCurrency->code ?>
                                </div>
                            </div>
                            <br/>
                    <?php endif; ?>
                        <div class="row">
                            <div class="col">
                                <?= $form->field($model, 'selling_currency_label')
                                    ->textInput([
                                        'maxlength' => true,
                                    ])
                                    ->label(Html::icon('private') . ' ' . $model->getAttributeLabel('selling_currency_label') . $labelOptional); ?>
                            </div>
                        </div>
                        <hr/>
                    <?php if ($model->isNewRecord): ?>
                        <div class="row">
                            <div class="col">
                                <?= $form->field($model, 'buying_currency_id')->widget(CurrencySelect::class); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <div class="col d-flex">
                                <strong><?= Yii::t('app', 'Buy') ?></strong>: <?= $model->buyingCurrency->code ?>
                            </div>
                        </div>
                        <br/>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'buying_currency_label')
                                ->textInput([
                                    'maxlength' => true,
                                ])
                                ->label(Html::icon('private') . ' ' . $model->getAttributeLabel('buying_currency_label') . $labelOptional); ?>
                        </div>
                    </div>
                    <hr/>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'selling_rate')
                                ->textInput([
                                    'id' => 'selling_rate',
                                    'autocomplete' => 'off',
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
                                    'autocomplete' => 'off',
                                    'maxlength' => true,
                                    'placeholder' => '∞',
                                ])
                                ->label($model->getAttributeLabel('buying_rate') . $labelOptional); ?>
                        </div>
                    </div>
                    <hr/>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'selling_currency_min_amount')
                                ->textInput([
                                    'autocomplete' => 'off',
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
                                    'autocomplete' => 'off',
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
                                <?= $form->field($model, 'selling_location')
                                    ->widget(LocationPickerWidget::class)
                                    ->label(Html::icon('private') . ' ' . $model->getAttributeLabel('selling_location'))
                                ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <?= $form->field($model, 'selling_delivery_radius')
                                    ->textInput([
                                        'maxlength' => true,
                                        'placeholder' => 0 . ', ' . Yii::t('app', 'No delivery'),
                                    ])
                                    ->label(Html::icon('private') . ' ' . Yii::t('app', 'Delivery radius') . ', km' . $labelOptional)
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
                                <?= $form->field($model, 'buying_location')
                                    ->widget(LocationPickerWidget::class)
                                    ->label(Html::icon('private') . ' ' . $model->getAttributeLabel('buying_location'))
                                ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <?= $form->field($model, 'buying_delivery_radius')
                                    ->textInput([
                                        'maxlength' => true,
                                        'placeholder' => 0 . ', ' . Yii::t('app', 'No delivery'),
                                    ])
                                    ->label(Html::icon('private') . ' ' . Yii::t('app', 'Delivery radius') . ', km' . $labelOptional)
                                ?>
                            </div>
                        </div>
                    </div>

                    <?php
                    $this->registerJs(<<<JS
                    const calculateCrossRate = (rate) => {
                        const curVal = parseFloat(rate);

                        if (!isNaN(curVal) && curVal !== 0) {
                            return (1/curVal).toFixed(8);
                        }

                        return '';
                    }

                    $('#selling_rate').on('input', function() {
                        $('#buying_rate').val(calculateCrossRate($(this).val()));
                    });

                    $('#buying_rate').on('input', function() {
                        $('#selling_rate').val(calculateCrossRate($(this).val()));
                    });

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

                    JS);
                    ?>

                </div>
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?php $cancelUrl = $model->isNewRecord ? Url::to('/currency-exchange-order/index') : Url::to(['/currency-exchange-order/view', 'id' => $model->id])?>
                    <?= CancelButton::widget(['url' => $cancelUrl]); ?>
                    <?php if (!$model->isNewRecord) : ?>
                        <?= DeleteButton::widget([
                            'url' => ['delete', 'id' => $model->id],
                            'options' => [
                                'data' => [
                                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                                    'method' => 'post'
                                ]
                            ]
                        ]); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
<?php
$locationMapActionUrl = Url::to(['/currency-exchange-order/location-map-modal']);
