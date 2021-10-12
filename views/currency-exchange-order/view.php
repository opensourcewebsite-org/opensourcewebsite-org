<?php

declare(strict_types=1);

use app\models\CurrencyExchangeOrder;
use app\models\PaymentMethod;
use app\widgets\buttons\EditButton;
use yii\helpers\ArrayHelper;
use app\components\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use app\widgets\ModalAjax;

/* @var $this yii\web\View */
/* @var $model app\models\CurrencyExchangeOrder */

$this->title = Yii::t('app', 'Order') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Currency Exchange'), 'url' => ['index']];
$this->params['breadcrumbs'][] = '#' . $model->id;

?>
<div class="index">
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
                                    'value' => $model->getTitle() . ($model->label ? '<br/><i>' . $model->label . '</i>' : ''),
                                    'format' => 'html',
                                ],
                                [
                                    'label' => Yii::t('app', 'Exchange rate'),
                                    'value' => function ($model) {
                                        return Yii::t('app', 'Cross Rate') . ($model->fee != 0 ? ' ' . $model->getFeeBadge() : '');
                                    },
                                    'format' => 'html',
                                ],
                                [
                                    'label' => Yii::t('app', 'Limits'),
                                    'value' => $model->getFormatLimits(),
                                ],
                                [
                                    'label' => Yii::t('app', 'Offers'),
                                    'visible' => $model->getMatchesCount(),
                                    'format' => 'raw',
                                    'value' => function () use ($model) {
                                        return $model->getMatchesCount() ?
                                            Html::a(
                                                $model->getNewMatchesCount() ? Html::badge('info', 'new') : $model->getMatchesCount(),
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

<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('app', 'Selling payment methods') ?></h3>
                    <div class="card-tools">
                        <?= ModalAjax::widget([
                            'id' => 'update-selling-payment-methods',
                            'header' => Yii::t('app', 'Update selling payment methods'),
                            'toggleButton' => [
                                'label' => Html::icon('edit'),
                                'title' => Yii::t('app', 'Edit'),
                                'class' => 'btn btn-light edit-btn',
                                'style' => [
                                    'float' => 'right',
                                ],
                            ],
                            'url' => Url::to([
                                'currency-exchange-order/update-selling-payment-methods',
                                'id' => $model->id,
                            ]),
                        ]);?>
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
                                            <?= $method->url ? Html::a($method->name, $method->url) : $method->name; ?>
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
</div>

<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('app', 'Buying payment methods') ?></h3>
                    <div class="card-tools">
                            <?= ModalAjax::widget([
                                'id' => 'update-buying-payment-methods',
                                'header' => Yii::t('app', 'Update buying payment methods'),
                                'toggleButton' => [
                                    'label' => Html::icon('edit'),
                                    'title' => Yii::t('app', 'Edit'),
                                    'class' => 'btn btn-light edit-btn',
                                    'style' => [
                                        'float' => 'right',
                                    ],
                                ],
                                'url' => Url::to([
                                    'currency-exchange-order/update-buying-payment-methods',
                                    'id' => $model->id,
                                ]),
                            ]);?>
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
                                            <?= $method->url ? Html::a($method->name, $method->url) : $method->name; ?>
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
</div>
<?php
$statusActiveUrl = Yii::$app->urlManager->createUrl(['currency-exchange-order/set-active?id=' . $model->id]);
$statusInactiveUrl = Yii::$app->urlManager->createUrl(['currency-exchange-order/set-inactive?id=' . $model->id]);

$script = <<<JS

$('.status-update').on("click", function(event) {
    const status = $(this).data('value');
    const active_url = '{$statusActiveUrl}';
    const inactive_url = '{$statusInactiveUrl}';
    const url = (parseInt(status) === 1) ? active_url : inactive_url;

        $.post(url, {}, function(result) {
            if (result === true) {
                location.reload();
            }
            else {

                $('#main-modal-header').text('Warning!');

                for (const [, errorMsg] of Object.entries(result)) {
                    $('#main-modal-body').append('<p>' + errorMsg + '</p>');
                }

                $('#main-modal').show();
                $('.close').on('click', function() {
                    $("#main-modal-body").html("");
                    $('#main-modal').hide();
                });
            }
        });

    return false;
});
JS;
$this->registerJs($script);
