<?php

use app\models\ContactGroup;
use app\widgets\buttons\EditButton;
use app\widgets\Modal;
use app\widgets\ModalAjax;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\components\helpers\Html;
use app\models\Contact;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\models\Contact */

$this->title = $contact->getContactName();
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contacts'), 'url' => ['index']];
?>
<?php if ($user) : ?>
<div class="public-view">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div id="w0" class="grid-view">
                            <table class="table table-condensed table-hover" style="margin-bottom: 0;">
                                <tbody>
                                    <tr>
                                        <th class="align-middle">User ID</th>
                                        <td class="align-middle">#<?= $user->id ?></td>
                                        <td></td>
                                    </tr>
                                    <?php if ($user->username) : ?>
                                        <tr>
                                            <th class="align-middle"><?= Yii::t('app', 'Username'); ?></th>
                                            <td class="align-middle">@<?= $user->username ?></td>
                                            <td></td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <th class="align-middle"><?= Yii::t('user', 'Rank'); ?></th>
                                        <td class="align-middle"><?= $user->getRank() ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"><?= Yii::t('user', 'Real confirmations'); ?></th>
                                        <td class="align-middle"><?= $user->getRealConfirmations() ?></td>
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
<?php endif; ?>

<div class="private-view">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <?php if ($contact->canHaveDebtRedistribution()) { ?>
                            <li class="nav-item align-self-center mr-4">
                                <?= ModalAjax::widget([
                                    'id' => 'modal-debt-redistribution-table',
                                    //`overflow-y` fix scroll after closing of sub-modal
                                    'options' => ['style' => 'overflow-y:scroll;'],
                                    'header'=> Yii::t('app', 'Debt Redistribution'),
                                    'toggleButton' => [
                                        'label' => Yii::t('app', 'Debt Redistribution'),
                                        'class' => 'btn btn-light',
                                    ],
                                    'url' => Url::to(['/debt-redistribution', 'contactId' => $contact->id]),
                                    'ajaxSubmit' => false,
                                    'events' => [
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
                                'options' => [
                                    'title' => 'Edit Contact',
                                ],
                                'url' => [
                                    'contact/update',
                                    'id' => $contact->id,
                                ],
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

                                        foreach ($contact->getGroups()->each() as $group) {
                                            $groups .= Html::tag('span', $group->name, ['class' => 'badge badge-primary']) . '&nbsp';
                                        }

                                        echo $groups;
                                        ?>
                                    </td>
                                    <td>
                                        <?= ModalAjax::widget([
                                            'id' => 'update-groups',
                                            'header' => Yii::t('app', 'Update groups'),
                                            'toggleButton' => [
                                                'class' => 'btn btn-light edit-btn',
                                                'label' => Html::icon('edit'),
                                                'style' => [
                                                    'float' => 'right',
                                                ],
                                                'title' => Yii::t('app', 'Update groups'),
                                            ],
                                            'url' => [
                                                '/contact/update-groups',
                                                'id' => $contact->id,
                                            ],
                                        ]) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Identification'); ?></th>
                                    <td class="align-middle"><?= $contact->getIsRealBadge(); ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= $contact->getAttributeLabel('relation'); ?></th>
                                    <td class="align-middle"><?= $contact->getRelationBadge(); ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= $contact->getAttributeLabel('vote_delegation_priority'); ?></th>
                                    <td class="align-middle"><?= $contact->vote_delegation_priority ?: Html::badge('secondary', Yii::t('app', 'DENY')); ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= $contact->getAttributeLabel('debt_redistribution_priority'); ?></th>
                                    <td class="align-middle"><?= $contact->debt_redistribution_priority ?: Html::badge('secondary', Yii::t('app', 'DENY')); ?></td>
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
