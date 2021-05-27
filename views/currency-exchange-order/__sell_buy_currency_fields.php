<?php

use app\models\Currency;
use app\models\CurrencyExchangeOrder;
use app\widgets\CurrencySelect\CurrencySelect;
use yii\widgets\ActiveForm;

/**
 * @var ActiveForm $form
 * @var CurrencyExchangeOrder $model
 */

?>

<div class="row">
    <div class="col">
        <?= $form->field($model, 'selling_currency_id')->widget(CurrencySelect::class); ?>
    </div>
</div>
<div class="row">
    <div class="col">
        <?= $form->field($model, 'buying_currency_id')->widget(CurrencySelect::class); ?>
    </div>
</div>
