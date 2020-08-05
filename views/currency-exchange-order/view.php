<?php

use \app\models\CurrencyExchangeOrder;
use app\widgets\buttons\EditButton;

/* @var $this yii\web\View */
/* @var $model app\models\CurrencyExchangeOrder */

$this->title = Yii::t('app', 'Currency Exchange Order') . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Currency Exchange Orders'), 'url' => ['index']];
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
                                    <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <?= $model->getAttributeLabel('Status'); ?>
                                    </a>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                    <a class="dropdown-item status-update <?= $model->status === CurrencyExchangeOrder::STATUS_ACTIVE ? 'active' : '' ?>" href="#" data-value="<?= CurrencyExchangeOrder::STATUS_ACTIVE ?>">Active</a>
                                    <a class="dropdown-item status-update <?= $model->status === CurrencyExchangeOrder::STATUS_INACTIVE ? 'active' : '' ?>" href="#" data-value="<?= CurrencyExchangeOrder::STATUS_INACTIVE ?>">Inactive</a>
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
                                <caption>Currency exchange orders</caption>
                                <tbody>
                                    <tr>
                                        <th class="align-middle" scope="col"><?= $model->getAttributeLabel('selling_currency_id') . '/' . $model->getAttributeLabel('buying_currency_id'); ?></th>
                                        <td class="align-middle"><?= $model->getSellingCurrency()->code . '/' . $model->getBuyingCurrency()->code; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle" scope="col"><?= $model->getAttributeLabel('selling_rate'); ?></th>
                                        <td class="align-middle"><?= $model->selling_rate; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle" scope="col"><?= $model->getAttributeLabel('buying_rate'); ?></th>
                                        <td class="align-middle"><?= $model->buying_rate; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle" scope="col"><?= $model->getAttributeLabel('selling_currency_min_amount'); ?></th>
                                        <td class="align-middle"><?= $model->selling_currency_min_amount; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle" scope="col"><?= $model->getAttributeLabel('selling_currency_max_amount'); ?></th>
                                        <td class="align-middle"><?= $model->selling_currency_max_amount; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle" scope="col"><?= $model->getAttributeLabel('delivery_radius'); ?></th>
                                        <td class="align-middle"><?= $model->delivery_radius; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle" scope="col"><?= Yii::t('app', 'Location'); ?></th>
                                        <td class="align-middle"><?= $model->location_lat . ', ' . $model->location_lon; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle" scope="col"><?= Yii::t('app', 'Payment method for Sell'); ?></th>
                                        <td class="align-middle"><?= $sellPayment; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle" scope="col"><?= Yii::t('app', 'Payment method for Buy'); ?></th>
                                        <td class="align-middle"><?= $buyPayment; ?></td>
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
                $('#main-modal-header').text('Warning!');
                $('#main-modal-body').html(response);
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
