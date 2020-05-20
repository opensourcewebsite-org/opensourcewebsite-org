<?php

use app\widgets\buttons\EditButton;
use app\widgets\ModalAjax;
use yii\helpers\Html;
use app\models\Contact;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Contact */

$this->title = Yii::t('app', 'View Contact');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contacts'), 'url' => ['index', 'view' => Contact::VIEW_USER]];
$this->params['breadcrumbs'][] = '#' . $model->id;

?>
<div class="contact-view">
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
                        <li class="nav-item align-self-center mr-4">
                            <?= EditButton::widget([
                                'url' => ['contact/update', 'id' => $model->id],
                                'options' => [
                                    'title' => 'Edit Contact'
                                ]
                            ]); ?>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'userIdOrName',
                                'value' => static function (Contact $model) {
                                    if (!empty($model->linkedUser)) {
                                        return !empty($model->linkedUser->username) ? '@' . $model->linkedUser->username : '#' . $model->linkedUser->id;
                                    }
                                },
                                'visible' => $model->link_user_id ? 1 : 0
                            ],
                            'name',
                            [
                                'label' => Yii::t('user', 'Real confirmations'),
                                'attribute' => 'Real Confirmations',
                                'value' => $realConfirmations,
                                'visible' => $model->link_user_id ? 1 : 0
                            ],
                            [
                                'attribute' => 'is_real',
                                'value' => function ($model) {
                                    return $model->is_real ? Yii::t('app', 'Yes') : Yii::t('app', 'No');
                                }
                            ],
                            [
                                'label' => Yii::t('app', 'Relation'),
                                'value' => Yii::t('app', Contact::RELATIONS[$model->relation]),
                            ],
                            'vote_delegation_priority',
                            'debt_redistribution_priority',
                        ],
                        'options' => ['class' => 'table table-hover detail-view']
                    ]); ?>
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
