<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Currency Exchange Orders';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="currency-exchange-order-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Currency Exchange Order', ['create'], ['class' => 'btn btn-success']) ?>
    </p>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'user_id',
            'selling_currency_id',
            'buying_currency_id',
            'selling_rate',
            //'buying_rate',
            //'selling_currency_min_amount',
            //'selling_currency_max_amount',
            //'status',
            //'renewed_at',
            //'delivery_radius',
            //'location_lat',
            //'location_lon',
            //'created_at',
            //'processed_at',
            //'selling_cash_on',
            //'buying_cash_on',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
