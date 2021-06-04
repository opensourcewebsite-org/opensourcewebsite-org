<?php

use app\helpers\LatLonHelper;
use app\models\CurrencyExchangeOrder;
use app\widgets\ContactWidget\ContactWidget;
use yii\web\View;

/**
 * @var View $this
 * @var CurrencyExchangeOrder $orderModel
 * @var CurrencyExchangeOrder $matchOrderModel
 */

$this->title = Yii::t('app', 'Offer');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Currency Exchange'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $orderModel->id, 'url' => ['view', 'id' => $orderModel->id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Offers'), 'url' => ['view-offers', 'id' => $orderModel->id]];
$this->params['breadcrumbs'][] = $matchOrderModel->id;

$buyingDistance = '';
$sellingDistance = '';

if ( $orderModel->selling_location && $matchOrderModel->buying_location ) {
    $buyingDistance = LatLonHelper::getCircleDistance(
        (float)$orderModel->selling_location_lat,
        (float)$orderModel->selling_location_lon,
        (float)$matchOrderModel->buying_location_lat,
        (float)$matchOrderModel->buying_location_lon
    );
    $buyingDistance =' (' . (string)round($buyingDistance) . ' km)';
}

if ( $orderModel->buying_location && $matchOrderModel->selling_location ) {
    $sellingDistance = LatLonHelper::getCircleDistance(
        (float)$orderModel->buying_location_lat,
        (float)$orderModel->buying_location_lon,
        (float)$matchOrderModel->selling_location_lat,
        (float)$matchOrderModel->selling_location_lon
    );
    $sellingDistance = ' (' . (string)round($sellingDistance) . ' km)';
}

?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <div id="w0" class="grid-view">
                        <table class="table table-condensed table-hover" style="margin-bottom: 0;">
                            <tbody>
                            <tr>
                                <th class="align-middle" scope="col" style="width: 50%;">
                                    <?= $matchOrderModel->getAttributeLabel('id') ?>
                                </th>
                                <td class="align-middle">
                                    <?= $matchOrderModel->id ?>
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <th class="align-middle" scope="col" style="width: 50%">
                                    <?= Yii::t('app', 'Sell') . ' / ' . Yii::t('app', 'Buy'); ?>
                                </th>
                                <td class="align-middle"><?= $matchOrderModel->sellingCurrency->code . ' / ' . $matchOrderModel->buyingCurrency->code; ?></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th class="align-middle"
                                    scope="col"><?= $matchOrderModel->getAttributeLabel('selling_rate'); ?></th>
                                <td class="align-middle">
                                    <?=
                                    !$matchOrderModel->cross_rate_on ?
                                        (round($matchOrderModel->selling_rate, 8) ?: '∞') :
                                        Yii::t('app', 'Cross Rate')
                                    ?>
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <th class="align-middle"
                                    scope="col"><?= $matchOrderModel->getAttributeLabel('buying_rate'); ?></th>
                                <td class="align-middle">
                                    <?=
                                    !$matchOrderModel->cross_rate_on ?
                                        (round($matchOrderModel->buying_rate, 8) ?: '∞') :
                                        Yii::t('app', 'Cross Rate')
                                    ?>
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <th class="align-middle"
                                    scope="col"><?= $matchOrderModel->getAttributeLabel('selling_currency_min_amount') ?></th>
                                <td class="align-middle"><?= $matchOrderModel->getSellingCurrencyMinAmount() ?></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th class="align-middle"
                                    scope="col"><?= $matchOrderModel->getAttributeLabel('selling_currency_max_amount') ?></th>
                                <td class="align-middle"><?= $matchOrderModel->getSellingCurrencyMaxAmount() ?></td>
                                <td></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= ContactWidget::widget(['user' => $matchOrderModel->user])?>

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
                                    <?= Yii::t('app', 'Cash') . $sellingDistance ?>
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
                                <td><?= Yii::t('app', 'Cash') . $buyingDistance ?></td>
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
