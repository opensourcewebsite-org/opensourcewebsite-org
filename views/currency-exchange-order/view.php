<?php

use \app\models\CurrencyExchangeOrder;
use app\models\PaymentMethod;
use app\widgets\buttons\EditButton;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\CurrencyExchangeOrder */

$this->title = Yii::t('app', 'Order') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Currency Exchange'), 'url' => ['index']];
$this->params['breadcrumbs'][] = '#' . $model->id;

?>

    <div class="currency-exchange-order-view">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex p-0">
                        <ul class="nav nav-pills ml-auto p-2">
                            <li class="nav-item align-self-center mr-3">
                                <div class="input-group-prepend">
                                    <div class="dropdown">
                                        <a class="btn <?= $model->isActive() ? 'btn-primary' : 'btn-default' ?> dropdown-toggle"
                                           href="#" role="button"
                                           id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true"
                                           aria-expanded="false">
                                            <?= $model->isActive() ?
                                                Yii::t('app', 'Active') :
                                                Yii::t('app', 'Inactive') ?>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                            <h6 class="dropdown-header"><?= $model->getAttributeLabel('Status') ?></h6>

                                            <a class="dropdown-item status-update <?= $model->isActive() ? 'active' : '' ?>"
                                               href="#"
                                               data-value="<?= CurrencyExchangeOrder::STATUS_ON ?>">
                                                <?= Yii::t('app', 'Active') ?>
                                            </a>

                                            <a class="dropdown-item status-update <?= !$model->isActive() ? 'active' : '' ?>"
                                               href="#"
                                               data-value="<?= CurrencyExchangeOrder::STATUS_OFF ?>">
                                                <?= Yii::t('app', 'Inactive') ?>
                                            </a>
                                        </div>
                                    </div>
                            </li>
                            <li class="nav-item align-self-center mr-3">
                                <?= EditButton::widget([
                                    'url' => ['currency-exchange-order/update', 'id' => $model->id],
                                    'options' => [
                                        'title' => 'Edit Currency Exchange Order',
                                    ]
                                ]); ?>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <div id="w0" class="grid-view">
                                <table class="table table-condensed table-hover" style="margin-bottom: 0;">
                                    <tbody>
                                    <tr>
                                        <th class="align-middle" scope="col" style="width: 50%;">
                                            <?= $model->getAttributeLabel('id') ?>
                                        </th>
                                        <td class="align-middle">
                                            <?= $model->id ?>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"
                                            scope="col">
                                            <?= Yii::t('app', 'Sell') . ' / ' . Yii::t('app', 'Buy'); ?>
                                        </th>
                                        <td class="align-middle">
                                            <?= $model->sellingCurrency->code . ' / ' . $model->buyingCurrency->code; ?>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"
                                            scope="col"><?= $model->getAttributeLabel('selling_rate'); ?></th>
                                        <td class="align-middle">
                                            <?=
                                            !$model->cross_rate_on ?
                                                (round($model->selling_rate, 8) ?: '∞') :
                                                Yii::t('app', 'Cross Rate')
                                            ?>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"
                                            scope="col"><?= $model->getAttributeLabel('buying_rate'); ?></th>
                                        <td class="align-middle">
                                            <?=
                                            !$model->cross_rate_on ?
                                                (round($model->buying_rate, 8) ?: '∞') :
                                                Yii::t('app', 'Cross Rate')
                                            ?>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"
                                            scope="col"><?= $model->getAttributeLabel('selling_currency_min_amount'); ?></th>
                                        <td class="align-middle"><?= $model->getSellingCurrencyMinAmount() ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"
                                            scope="col"><?= $model->getAttributeLabel('selling_currency_max_amount'); ?></th>
                                        <td class="align-middle"><?= $model->getSellingCurrencyMaxAmount() ?></td>
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
    </div>

<?php if ($offersCount = $model->getMatchesOrderedByUserRating()->count()) : ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div id="w0" class="grid-view">
                            <table class="table table-condensed table-hover" style="margin-bottom: 0;">
                                <tbody>
                                <tr>
                                    <th class="align-middle" scope="col"><?= Yii::t('app', 'Offers') ?></th>
                                    <td class="align-middle">
                                        <?= Html::a($offersCount, Url::to(['view-offers', 'id' => $model->id])) ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('app', 'Selling payment methods') ?></h3>
                    <div class="card-tools">
                        <a class="edit-btn modal-btn-ajax"
                           href="/currency-exchange-order/update-sell-methods/<?= $model->id ?>"
                           title="Edit" style="float: right">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div id="w0" class="grid-view">
                            <table class="table table-condensed table-hover" style="margin-bottom: 0;">
                                <tbody>
                                <?php if ($model->selling_cash_on) : ?>
                                    <tr>
                                        <td><?= Yii::t('app', 'Cash') ?> (
                                            <?= Yii::t('app', 'Location'); ?>: <?= Html::a($model->selling_location, Url::to(['view-order-selling-location', 'id' => $model->id]), ['class' => 'modal-btn-ajax']) ?>
                                            <?php if ($model->selling_delivery_radius): ?>
                                                | <?= Yii::t('app', 'Delivery radius'); ?>: <?= $model->selling_delivery_radius; ?> <?= Yii::t('app', 'km'); ?>
                                            <?php endif; ?>
                                            )
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php foreach ($model->sellingPaymentMethods as $method) : ?>
                                    <?php /** @var PaymentMethod $method */ ?>
                                    <tr>
                                        <td>
                                            <?= $method->name ?>
                                        </td>
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
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('app', 'Buying payment methods') ?></h3>
                    <div class="card-tools">
                        <a class="edit-btn modal-btn-ajax"
                           href="/currency-exchange-order/update-buy-methods/<?= $model->id ?>"
                           title="Edit" style="float: right">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div id="w0" class="grid-view">
                            <table class="table table-condensed table-hover" style="margin-bottom: 0;">
                                <tbody>
                                <?php if ($model->buying_cash_on) : ?>
                                    <tr>
                                        <td><?= Yii::t('app', 'Cash') ?> (
                                            <?= Yii::t('app', 'Location'); ?>: <?= Html::a($model->buying_location, Url::to(['view-order-buying-location', 'id' => $model->id]), ['class' => 'modal-btn-ajax']) ?>
                                            <?php if ($model->buying_delivery_radius): ?>
                                                | <?= Yii::t('app', 'Delivery radius'); ?>: <?= $model->buying_delivery_radius; ?> <?= Yii::t('app', 'km'); ?>
                                            <?php endif; ?>
                                            )
                                        </td>
                                </tr>
                                <?php endif; ?>
                                <?php foreach ($model->buyingPaymentMethods as $method) : ?>
                                    <?php /** @var PaymentMethod $method */ ?>
                                    <tr>
                                        <td>
                                            <?= $method->name ?>
                                        </td>
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

<?php
$url = Yii::$app->urlManager->createUrl(['currency-exchange-order/status?id=' . $model->id]);
$script = <<<JS

$('.status-update').on("click", function(event) {
    var status = $(this).data('value');
        $.post('{$url}', {'status': status}, function(result) {
            if (result === "1") {
                location.reload();
            }
            else {
                var response = $.parseJSON(result);
                console.log(response);
                $('#main-modal-header').text('Warning!');
                response.map(function(line) { $('#main-modal-body').append('<p>' + line + '</p>') })

                $('#main-modal').show();
                $('.close').on('click', function() {
                    $("#main-modal-body").html("");
                    $('#main-modal').hide();
                });
                // alert('Sorry, there was an error while trying to change status');
            }
        });

    return false;
});
JS;
$this->registerJs($script);
