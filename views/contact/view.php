<?php

use app\models\ContactGroup;
use app\widgets\buttons\EditButton;
use app\widgets\Modal;
use app\widgets\ModalAjax;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\models\Contact;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Contact */
/* @var $realConfirmations int count of real Contacts */

$this->title = Yii::t('app', 'View Contact');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contacts'), 'url' => ['index', 'view' => Contact::VIEW_USER]];
$this->params['breadcrumbs'][] = '#' . $model->id;

?>


    <div class="contacts-view">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex p-0">
                        <ul class="nav nav-pills ml-auto p-2">
                            <?php if ($model->canHaveDebtRedistribution()) { ?>
                                <li class="nav-item align-self-center mr-4">
                                    <?= ModalAjax::widget([
                                        'id'           => 'modal-debt-redistribution-table',
                                        //`overflow-y` fix scroll after closing of sub-modal
                                        'options'      => ['style' => 'overflow-y:scroll;'],
                                        'header'       => Yii::t('app', 'Debt Redistribution'),
                                        'toggleButton' => [
                                            'label' => Yii::t('app', 'Debt Redistribution'),
                                            'class' => 'btn btn-light',
                                        ],
                                        'url'          => Url::to(['/debt-redistribution', 'contactId' => $model->id]),
                                        'ajaxSubmit'   => false,
                                        'events'       => [
                                            ModalAjax::EVENT_MODAL_SHOW_COMPLETE => new JsExpression('
                                            function(event, xhr, textStatus) {
                                                if (xhr.status >= 400) {
                                                    jQuery(this).modal("toggle");
                                                    osw_alertOnAjaxError(xhr, textStatus);
                                                }
                                            }
                                        '),
                                        ],
                                    ]);
                                    ?>
                                </li>
                            <?php } ?>
                            <li class="nav-item align-self-center mr-3">
                                <?= EditButton::widget([
                                    'url' => ['contact/update', 'id' => $model->id],
                                    'options' => [
                                        'title' => 'Edit Contact',
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
                                        <th class="align-middle">Groups</th>
                                        <td class="align-middle">
                                            <?php
                                            $groups = '';

                                            foreach ($model->getContactGroups()->each() as $group) {
                                                $groups .= Html::tag('span', $group->name, ['class' => 'badge badge-primary']) . "\n";
                                            }
                                            echo $groups;
                                            ?>
                                        </td>
                                        <?= ModalAjax::widget([
                                            'header' => Yii::t('app', 'Update groups'),
                                            'id' => 'groups-modal',
                                            'url' => ['update-contact-groups', 'id' => $model->id],
                                        ]); ?>

                                        <td><?= EditButton::widget([
                                                'url' => '#',
                                                'options' => [
                                                    'data-toggle' => 'modal',
                                                    'data-target' => '#groups-modal',
                                                    'class' => 'text-priority float-right',
                                                ],
                                            ]); ?></td>
                                    </tr>
                                    <?php if ($model->link_user_id) : ?>
                                        <tr>
                                            <th class="align-middle"><?= $model->getAttributeLabel('userIdOrName'); ?></th>
                                            <td class="align-middle"><?= $model->userIdOrName; ?></td>
                                            <td></td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <th class="align-middle"><?= $model->getAttributeLabel('name'); ?></th>
                                        <td class="align-middle"><?= $model->name; ?></td>
                                        <td></td>
                                    </tr>
                                    <?php if ($model->link_user_id) : ?>
                                        <tr>
                                            <th class="align-middle"><?= $model->getAttributeLabel('is_real'); ?></th>
                                            <td class="align-middle"><?= $realConfirmations; ?></td>
                                            <td></td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <th class="align-middle"><?= $model->getAttributeLabel('relation'); ?></th>
                                        <td class="align-middle"><?= Contact::RELATIONS[$model->relation]; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"><?= $model->getAttributeLabel('vote_delegation_priority'); ?></th>
                                        <td class="align-middle"><?= $model->vote_delegation_priority; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"><?= $model->getAttributeLabel('debt_redistribution_priority'); ?></th>
                                        <td class="align-middle"><?= $model->renderDebtRedistributionPriority(); ?></td>
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
$this->registerJs(<<<JS
/**
 * @param jqXHR
 * @param {?string} textStatus
 * @param {?string} errorThrown
 */
function osw_alertOnAjaxError(jqXHR, textStatus, errorThrown) {
    let errTitle = textStatus ? textStatus : jqXHR.statusText;
    if (errorThrown) {
        errTitle += "\\n" + errorThrown;
    }
    errTitle += "\\nReload page, please.";
    if (!jqXHR.responseJSON && jqXHR.responseText) {
        errTitle += "\\n\\n" + jqXHR.responseText;
    }
    alert(errTitle);
    console.log(errTitle, jqXHR.responseJSON ? jqXHR.responseJSON : jqXHR.responseText);
}
JS
, View::POS_END);
