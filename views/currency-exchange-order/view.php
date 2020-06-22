<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\CurrencyExchangeOrder */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Currency Exchange Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="currency-exchange-order-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'user_id',
            'selling_currency_id',
            'buying_currency_id',
            'selling_rate',
            'buying_rate',
            'selling_currency_min_amount',
            'selling_currency_max_amount',
            'status',
            'renewed_at',
            'delivery_radius',
            'location_lat',
            'location_lon',
            'created_at',
            'processed_at',
            'selling_cash_on',
            'buying_cash_on',
        ],
    ]) ?>

</div>
