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
/* @var $model CurrencyExchangeOrder */

$this->title = Yii::t('app', 'Offers');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Currency Exchange'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Matched Offers');

?>

<div class="currency-exchange-order-matches">
    <div class="row">
        <div class="col-12">
            <div class="card">
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
                                        (round($model->selling_rate, 8) ?: '∞') :
                                        Yii::t('app', 'Cross Rate');
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'buying_rate',
                                'value' => function ($model) {
                                    return !$model->cross_rate_on ?
                                        (round($model->buying_rate, 8) ?: '∞') :
                                        Yii::t('app', 'Cross Rate');
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'selling_currency_min_amount',
                                'value' => function ($model) {
                                    return $model->getSellingCurrencyMinAmount();
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'selling_currency_max_amount',
                                'value' => function ($model) {
                                    return $model->getSellingCurrencyMaxAmount();
                                },
                                'enableSorting' => false,
                            ],

                            [
                                'class' => ActionColumn::class,
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url, $offer_order) use ($model) {

                                        $icon = Html::tag('span', '', ['class' => 'fa fa-eye', 'data-toggle' => 'tooltip', 'title' => 'view']);
                                        return Html::a(
                                            $icon,
                                            Url::to(['view-match', 'order_id' => $model->id, 'match_order_id' => $offer_order->id]),
                                            ['class' => 'btn btn-outline-primary']
                                        );
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
