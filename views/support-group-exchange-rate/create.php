<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroupExchangeRate */

$this->title = 'Create Support Group Exchange Rate';
$this->params['breadcrumbs'][] = ['label' => 'Support Group Exchange Rates', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="support-group-exchange-rate-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
