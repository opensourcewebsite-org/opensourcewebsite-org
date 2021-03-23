<?php

use app\components\helpers\ArrayHelper;
use kartik\select2\Select2;

/* @var $form \yii\widgets\ActiveForm */
/* @var $model \app\models\CurrencyExchangeOrder */
/* @var $currencies \app\models\Currency[] */

$data = [];

foreach($currencies as $currency) {
    $data[$currency->id] = $currency->code . ' - ' . $currency->name;
}
?>

<div class="row">
    <div class="col">
        <?= $form->field($model, 'selling_currency_id')->widget(Select2::class, [
            'data' => $data,
            'options' => [
                'prompt' => '',
            ],
        ]); ?>
    </div>
</div>
<div class="row">
    <div class="col">
        <?= $form->field($model, 'buying_currency_id')->widget(Select2::class, [
            'data' => $data,
            'options' => [
                'prompt' => '',
            ],
        ]); ?>
    </div>
</div>
