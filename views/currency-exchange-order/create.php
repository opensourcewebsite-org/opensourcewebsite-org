<?php

use app\models\Currency;
use app\models\CurrencyExchangeOrder;
use app\models\PaymentMethod;

/* @var yii\web\View $this */
/* @var CurrencyExchangeOrder $model  */
/* @var array $_params_ */

$this->title = Yii::t('app', 'Create Order');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Currency Exchange'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="currency-exchange-order-create">
    <?= $this->render('_form', $_params_); ?>
</div>
