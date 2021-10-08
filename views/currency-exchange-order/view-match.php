<?php

declare(strict_types=1);

use app\helpers\LatLonHelper;
use app\models\CurrencyExchangeOrder;
use app\widgets\ContactWidget\ContactWidget;
use yii\web\View;
use yii\widgets\DetailView;
use app\components\helpers\Html;

/**
 * @var View $this
 * @var CurrencyExchangeOrder $orderModel
 * @var CurrencyExchangeOrder $matchOrderModel
 */

$this->title = Yii::t('app', 'Order') . ' #' . $matchOrderModel->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Currency Exchange'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $orderModel->id, 'url' => ['view', 'id' => $orderModel->id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Matched Offers'), 'url' => ['show-matches', 'id' => $orderModel->id]];
$this->params['breadcrumbs'][] = '#' . $matchOrderModel->id;

$buyingDistance = '';
$sellingDistance = '';

if ($orderModel->selling_location && $matchOrderModel->buying_location) {
    $buyingDistance = LatLonHelper::getCircleDistance(
        (float)$orderModel->selling_location_lat,
        (float)$orderModel->selling_location_lon,
        (float)$matchOrderModel->buying_location_lat,
        (float)$matchOrderModel->buying_location_lon
    );
    $buyingDistance = (string)round($buyingDistance);
}

if ($orderModel->buying_location && $matchOrderModel->selling_location) {
    $sellingDistance = LatLonHelper::getCircleDistance(
        (float)$orderModel->buying_location_lat,
        (float)$orderModel->buying_location_lon,
        (float)$matchOrderModel->selling_location_lat,
        (float)$matchOrderModel->selling_location_lon
    );
    $sellingDistance = (string)round($sellingDistance);
}

$model = $matchOrderModel;
?>
<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <?= DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                'id',
                                [
                                    'label' => Yii::t('app', 'Sell') . ' / ' . Yii::t('app', 'Buy'),
                                    'value' => $model->getTitle(),
                                ],
                                [
                                    'label' => Yii::t('app', 'Exchange rate'),
                                    'value' => function ($model) {
                                        return Yii::t('app', 'Cross Rate') . ($model->fee != 0 ? ' ' . $model->getFeeBadge(false) : '');
                                    },
                                    'format' => 'html',
                                ],
                                [
                                    'label' => Yii::t('app', 'Limits'),
                                    'value' => $model->getFormatLimits(),
                                ],
                            ]
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('app', 'Selling payment methods') ?></h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-condensed table-hover" style="margin-bottom: 0;">
                            <tbody>
                            <?php if ($matchOrderModel->selling_cash_on) : ?>
                                <tr>
                                    <td>
                                        <?= Yii::t('app', 'Cash') ?>
                                        <?php if ($sellingDistance): ?>
                                            ( <?= Yii::t('app', 'Distance'); ?>: <?= $sellingDistance ?> <?= Yii::t('app', 'km'); ?> )
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($matchOrderModel->getSellingPaymentMethods()->asArray()->all() as $method) : ?>
                                <tr>
                                    <td><?= $method['name'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('app', 'Buying payment methods') ?></h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-condensed table-hover">
                            <tbody>
                            <?php if ($matchOrderModel->selling_cash_on): ?>
                                <tr>
                                    <td>
                                        <?= Yii::t('app', 'Cash') ?>
                                        <?php if ($buyingDistance): ?>
                                            ( <?= Yii::t('app', 'Distance'); ?>: <?= $buyingDistance ?> <?= Yii::t('app', 'km'); ?> )
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($matchOrderModel->getBuyingPaymentMethods()->asArray()->all() as $method) : ?>
                                <tr>
                                    <td><?= $method['name'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= ContactWidget::widget(['user' => $matchOrderModel->user])?>
