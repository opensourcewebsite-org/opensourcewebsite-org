<?php

/* @var $this yii\web\View */
/* @var $model app\models\CurrencyExchangeOrder */

$this->title = Yii::t('app', 'Create Currency Exchange Order');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Currency Exchange Orders'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="currency-exchange-order-create">

    <?= $this->render('_form', [
        'model' => $model,
        'currency' => $currency,
    ]); ?>

</div>
