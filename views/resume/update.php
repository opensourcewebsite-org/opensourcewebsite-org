<?php

/* @var $this yii\web\View */
/* @var $model app\models\CurrencyExchangeOrder */
/* @var $currencies Currency[] */

use app\models\Currency;

$this->title = Yii::t('app', 'Update Resume') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Resume'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');

?>
<div class="currency-exchange-order-update">
    <?= $this->render('_form', [
        'model' => $model,
        'currencies' => $currencies,
    ]); ?>
</div>
