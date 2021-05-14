<?php
declare(strict_types=1);

use \app\models\CurrencyExchangeOrder;
use app\models\Resume;
use app\widgets\buttons\EditButton;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\CurrencyExchangeOrder */

$this->title = Yii::t('app', 'Resume') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Resume'), 'url' => ['index']];
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
                                               data-value="<?= Resume::STATUS_ON ?>">
                                                <?= Yii::t('app', 'Active') ?>
                                            </a>

                                            <a class="dropdown-item status-update <?= $model->isActive() ? '' : 'active' ?>"
                                               href="#"
                                               data-value="<?= Resume::STATUS_OFF ?>">
                                                <?= Yii::t('app', 'Inactive') ?>
                                            </a>
                                        </div>
                                    </div>
                            </li>
                            <li class="nav-item align-self-center mr-3">
                                <?= EditButton::widget([
                                    'url' => ['resume/update', 'id' => $model->id],
                                    'options' => [
                                        'title' => 'Edit Resume',
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
$url = Yii::$app->urlManager->createUrl(['resume/status?id=' . $model->id]);
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
