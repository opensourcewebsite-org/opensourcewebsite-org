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
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                    <?= $model->getAttributeLabel('Status'); ?>
                                </button>
                                <div class="dropdown-menu">
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
                                <tbody>
                                    <tr>
                                        <th class="align-middle"><?= $model->getAttributeLabel('selling_currency_id').'/'.$model->getAttributeLabel('buying_currency_id'); ?></th>
                                        <td class="align-middle"><?= $model->getSellingCurrency()->code . '/' . $model->getBuyingCurrency()->code; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"><?= $model->getAttributeLabel('selling_rate'); ?></th>
                                        <td class="align-middle"><?= $model->selling_rate; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"><?= $model->getAttributeLabel('buying_rate'); ?></th>
                                        <td class="align-middle"><?= $model->buying_rate; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"><?= $model->getAttributeLabel('selling_currency_min_amount'); ?></th>
                                        <td class="align-middle"><?= $model->selling_currency_min_amount; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"><?= $model->getAttributeLabel('selling_currency_max_amount'); ?></th>
                                        <td class="align-middle"><?= $model->selling_currency_max_amount; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"><?= $model->getAttributeLabel('delivery_radius'); ?></th>
                                        <td class="align-middle"><?= $model->delivery_radius; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"><?= Yii::t('app', 'Location'); ?></th>
                                        <td class="align-middle"><?= $model->location_lat . ', ' . $model->location_lon; ?></td>
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

    if (confirm('Are you sure you want to change this status?')) {
        $.post('{$url}', {'status': status}, function(result) {
            if (result === "1") {
                location.reload();
            }
            else {
                alert('Sorry, there was an error while trying to change status');
            }
        });
    }

    return false;
});
JS;
$this->registerJs($script);

