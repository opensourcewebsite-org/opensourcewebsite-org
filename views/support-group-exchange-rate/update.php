<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroupExchangeRate */

$this->title = 'Update Support Group Exchange Rate: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Support Group Exchange Rates', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="support-group-exchange-rate-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
