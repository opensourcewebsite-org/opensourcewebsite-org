<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\CurrencyExchangeOrder */

$this->title = 'Create Currency Exchange Order';
$this->params['breadcrumbs'][] = ['label' => 'Currency Exchange Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="currency-exchange-order-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
