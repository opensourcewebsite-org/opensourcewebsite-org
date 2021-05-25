<?php

/* @var $this yii\web\View */
/* @var $model app\models\CurrencyExchangeOrder */
/* @var $currencies Currency[] */
/* @var array $_params_ */

use app\models\Currency;
use app\models\PaymentMethod;

$this->title = Yii::t('app', 'Update Order') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Currency Exchange'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');

?>
<div class="currency-exchange-order-update">
    <?= $this->render('_form', $_params_); ?>
</div>
