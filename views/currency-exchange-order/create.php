<?php

use app\models\Currency;
use app\models\CurrencyExchangeOrder;
use app\models\PaymentMethod;

/* @var $this yii\web\View */
/* @var $model CurrencyExchangeOrder */
/* @var $currencies Currency */

$this->title = Yii::t('app', 'Create Order');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Currency Exchange'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="currency-exchange-order-create">

    <?= $this->render('_form', [
        'model' => $model,
        'currencies' => $currencies,
    ]); ?>

</div>
