<?php

/* @var $this yii\web\View */
/* @var $model app\models\CurrencyExchangeOrder */

$this->title = Yii::t('app', 'Update Currency Exchange Order ' . $model->id);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Currency Exchange Order'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');

?>
<div class="currency-exchange-order-update">
    <?= $this->render('_form2', [
        'model' => $model,
        'sellPaymentId' => isset($sellPaymentId)?$sellPaymentId:'',
        'buyPaymentId' => isset($buyPaymentId)?$buyPaymentId:'',
        'paymentsTypes' => $paymentsTypes,
    ]); ?>
</div>
