<?php

use app\models\Currency;
use app\widgets\buttons\AddButton;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\models\CurrencyExchangeOrder;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $view int */

$this->title = Yii::t('app', 'Currency Exchange');
$this->params['breadcrumbs'][] = $this->title;

$displayActiveOrders = (int)Yii::$app->request->get('status', CurrencyExchangeOrder::STATUS_ON) === CurrencyExchangeOrder::STATUS_ON;

$offersCol = $displayActiveOrders  ?
    [
        'label' => Yii::t('app', 'Offers'),
        'value' => function ($model) {
            return  $model->getMatches()->exists() ?
                Html::a($model->getMatches()->count(), Url::to(['show-matches', 'id' => $model->id])) :
                '';
        },
        'format' => 'raw',
        'enableSorting' => false,
    ]: [];
?>
<div class="currency-exchange-order-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item">
                            <?= Html::a(
    Yii::t('app', 'Active'),
    ['/currency-exchange-order', 'status' => CurrencyExchangeOrder::STATUS_ON],
    [
                                    'class' => 'nav-link show ' .
                                        ($displayActiveOrders ? 'active' : '')
                                ]
);
                            ?>
                        </li>
                        <li class="nav-item">
                            <?= Html::a(
                                Yii::t('app', 'Inactive'),
                                ['/currency-exchange-order', 'status' => CurrencyExchangeOrder::STATUS_OFF],
                                [
                                    'class' => 'nav-link show ' .
                                        (!$displayActiveOrders ? 'active' : '')
                                ]
                            );
                            ?>
                        </li>
                        <li class="nav-item align-self-center mr-4">
                            <?= AddButton::widget([
                                'url' => ['currency-exchange-order/create'],
                                'options' => [
                                    'title' => 'New Order',
                                ]
                            ]); ?>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            'id',
                            [
                                'label' => Yii::t('app', 'Sell') . ' / ' . Yii::t('app', 'Buy'),
                                'value' => function ($model) {
                                    $sellCurrency = Currency::findOne($model->selling_currency_id);
                                    $buyCurrency = Currency::findOne($model->buying_currency_id);

                                    return $sellCurrency->code . ' / ' . $buyCurrency->code;
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'selling_rate',
                                'value' => function ($model) {
                                    return !$model->cross_rate_on ?
                                        ($model->selling_rate ? round($model->selling_rate, 8) . ' ' . $model->buyingCurrency->code : '∞') :
                                        Yii::t('app', 'Cross Rate');
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'buying_rate',
                                'value' => function ($model) {
                                    return !$model->cross_rate_on ?
                                        ($model->buying_rate ? round($model->buying_rate, 8) . ' ' . $model->sellingCurrency->code : '∞') :
                                        Yii::t('app', 'Cross Rate');
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'selling_currency_min_amount',
                                'value' => function ($model) {
                                    return $model->selling_currency_min_amount ? number_format($model->selling_currency_min_amount, 2) . ' ' . $model->sellingCurrency->code : '∞';
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'selling_currency_max_amount',
                                'value' => function ($model) {
                                    return $model->selling_currency_max_amount ? number_format($model->selling_currency_max_amount, 2) . ' ' . $model->sellingCurrency->code : '∞';
                                },
                                'enableSorting' => false,
                            ],
                            $offersCol,
                            [
                                'class' => ActionColumn::class,
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url) {
                                        $icon = Html::tag('span', '', ['class' => 'fa fa-eye', 'data-toggle' => 'tooltip', 'title' => 'view']);
                                        return Html::a($icon, $url, ['class' => 'btn btn-outline-primary',]);
                                    },
                                ],
                            ],
                        ],
                        'layout' => "{summary}\n{items}\n<div class='card-footer clearfix'>{pager}</div>",
                        'pager' => [
                            'options' => [
                                'class' => 'pagination float-right',
                            ],
                            'linkContainerOptions' => [
                                'class' => 'page-item',
                            ],
                            'linkOptions' => [
                                'class' => 'page-link',
                            ],
                            'maxButtonCount' => 5,
                            'disabledListItemSubTagOptions' => [
                                'tag' => 'a',
                                'class' => 'page-link',
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
