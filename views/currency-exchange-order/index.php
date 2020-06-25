<?php

use app\widgets\buttons\AddButton;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\models\CurrencyExchangeOrder;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $view int */

$this->title = Yii::t('app', 'Currency Exchange Orders');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="currency-exchange-order-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Active'), ['/currency-exchange-order', 'status' => CurrencyExchangeOrder::STATUS_ACTIVE], [
                                'class' => 'nav-link show ' .
                                    (Yii::$app->request->get('status', (string) CurrencyExchangeOrder::STATUS_ACTIVE) === (string) CurrencyExchangeOrder::STATUS_ACTIVE ? 'active' : '')
                            ]); ?>
                        </li>
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Inactive'), ['/currency-exchange-order', 'status' => CurrencyExchangeOrder::STATUS_INACTIVE], [
                                'class' => 'nav-link show ' .
                                    (Yii::$app->request->get('status') === (string) CurrencyExchangeOrder::STATUS_INACTIVE ? 'active' : '')
                            ]); ?>
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
                            [
                                'attribute' => 'selling_rate',
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'buying_rate',
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'selling_currency_min_amount',
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'selling_currency_max_amount',
                                'enableSorting' => false,
                            ],
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
