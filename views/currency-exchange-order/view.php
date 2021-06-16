<?php

use \app\models\CurrencyExchangeOrder;
use app\models\PaymentMethod;
use app\widgets\buttons\EditButton;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

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
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    'id',
                                    [
                                        'label' => Yii::t('app', 'Sell') . ' / ' . Yii::t('app', 'Buy'),
                                        'value' => $model->sellingCurrency->code . ' / ' . $model->buyingCurrency->code,
                                    ],
                                    [
                                        'label' => Yii::t('app', 'Exchange rate'),
                                        'value' => Yii::t('app', 'Cross Rate') . ($model->fee != 0 ? ($model->fee > 0 ? ' +' : ' ') . (float)$model->fee . ' %' : ''),
                                    ],
                                    [
                                        'attribute' => 'selling_currency_min_amount',
                                        'value' => $model->selling_currency_min_amount ? number_format($model->selling_currency_min_amount, 2) . ' ' . $model->sellingCurrency->code : '∞',
                                    ],
                                    [
                                        'attribute' => 'selling_currency_max_amount',
                                        'value' => $model->selling_currency_max_amount ? number_format($model->selling_currency_max_amount, 2) . ' ' . $model->sellingCurrency->code : '∞',
                                    ],
                                    [
                                        'attribute' => 'label',
                                        'visible' => (bool)$model->label,
                                    ],
                                    [
                                        'label' => Yii::t('app', 'Offers'),
                                        'visible' => $model->getMatchesOrderedByUserRating()->count(),
                                        'format' => 'raw',
                                        'value' => function () use ($model) {
                                            return $model->getMatchesOrderedByUserRating()->count() ?
                                                Html::a(
                                                    $model->getMatchesOrderedByUserRating()->count(),
                                                    Url::to(['show-matches', 'id' => $model->id]),
                                                ) : '';
                                        },
                                    ],
                                ]
                            ]) ?>
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
$statusActiveUrl = Yii::$app->urlManager->createUrl(['currency-exchange-order/set-active?id=' . $model->id]);
$statusInactiveUrl = Yii::$app->urlManager->createUrl(['currency-exchange-order/set-inactive?id=' . $model->id]);

$script = <<<JS

$('.status-update').on("click", function(event) {
    const status = $(this).data('value');
    const statusActiveUrl = '{$statusActiveUrl}';
    const statusInactiveUrl = '{$statusInactiveUrl}';
    const url = (parseInt(status) === 1) ? statusActiveUrl : statusInactiveUrl;

        $.post(url, {'status': status}, function(result) {
            if (result === true) {
                location.reload();
            }
            else {
                $('#main-modal-header').text('Warning!');

                result.map(function(line) { $('#main-modal-body').append('<p>' + line + '</p>') })

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
