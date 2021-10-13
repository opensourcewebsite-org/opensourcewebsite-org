<?php

declare(strict_types=1);

use app\models\Currency;
use app\widgets\buttons\AddButton;
use app\components\helpers\Html;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\models\CurrencyExchangeOrder;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model CurrencyExchangeOrder */

$this->title = Yii::t('app', 'Offers');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Currency Exchange'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Matched Offers');
?>
<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card">
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
                                'label' => Yii::t('app', 'Sell'),
                                'value' => function ($model) {
                                    return $model->sellingCurrency->code;
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'label' => Yii::t('app', 'Buy'),
                                'value' => function ($model) {
                                    return $model->buyingCurrency->code;
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'label' => Yii::t('app', 'Exchange rate'),
                                'value' => function ($model) {
                                    return Yii::t('app', 'Cross Rate') . ($model->fee != 0 ? ' ' . $model->getFeeBadge(false) : '');
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
                                'class' => ActionColumn::class,
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url, CurrencyExchangeOrder $matchModel) use ($model) {
                                        return Html::a(
                                            $matchModel->isNewMatch() ? Html::badge('info', 'new') : Html::icon('eye'),
                                            Url::to(['view-match', 'order_id' => $model->id, 'match_order_id' => $matchModel->id]),
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
