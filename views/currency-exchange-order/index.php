<?php

declare(strict_types=1);

use app\models\Currency;
use app\widgets\buttons\AddButton;
use app\components\helpers\Html;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\models\CurrencyExchangeOrder;
use app\models\search\CurrencyExchangeOrderSearch;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $view int */

$this->title = Yii::t('app', 'Currency Exchange');
$this->params['breadcrumbs'][] = $this->title;

$displayActiveTab = $searchModel->status === CurrencyExchangeOrderSearch::STATUS_ON;
?>
<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <div class="col-sm-6">
                        <ul class="nav nav-pills ml-auto p-2">
                            <li class="nav-item">
                                <?= Html::a(
    Yii::t('app', 'Active'),
    ['/currency-exchange-order', 'CurrencyExchangeOrderSearch[status]' => CurrencyExchangeOrderSearch::STATUS_ON],
    [
                                        'class' => 'nav-link show ' . ($displayActiveTab ? 'active' : '')
                                    ]
);
                                ?>
                            </li>
                            <li class="nav-item">
                                <?= Html::a(
                                    Yii::t('app', 'Inactive'),
                                    ['/currency-exchange-order', 'CurrencyExchangeOrderSearch[status]' => CurrencyExchangeOrderSearch::STATUS_OFF],
                                    [
                                        'class' => 'nav-link show ' . (!$displayActiveTab ? 'active' : ''),
                                    ]
                                );
                                ?>
                            </li>
                        </ul>
                    </div>
                    <div class="col-sm-6">
                        <?= AddButton::widget([
                            'url' => ['currency-exchange-order/create'],
                            'options' => [
                                'title' => 'New Order',
                                'style' => [
                                    'float' => 'right',
                                ],
                            ]
                        ]); ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'enableSorting' => false,
                            ],
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
                                'label' => Yii::t('app', 'Exchange rate'),
                                'value' => function ($model) {
                                    return Yii::t('app', 'Cross Rate') . ($model->fee != 0 ? ' ' . $model->getFeeBadge() : '');
                                },
                                'format' => 'html',
                                'enableSorting' => false,
                            ],
                            [
                                'label' => Yii::t('app', 'Limits'),
                                'value' => function ($model) {
                                    return $model->getFormatLimits();
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'label',
                                'enableSorting' => false,
                            ],
                            [
                                'label' => Yii::t('app', 'Offers'),
                                'value' => function ($model) {
                                    return $model->getMatchesCount() ?
                                        Html::a(
                                            $model->getNewMatchesCount() ? Html::badge('info', 'new') : $model->getMatchesCount(),
                                            Url::to(['show-matches', 'id' => $model->id])
                                        ) : '';
                                },
                                'format' => 'raw',
                                'enableSorting' => false,
                                'visible' => $displayActiveTab,
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
