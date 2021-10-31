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
use yii\grid\ActionColumn;
use app\widgets\buttons\AddButton;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\Contact */

$this->title = !$contact->isNewRecord ? $contact->getContactName() : $user->id;
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
                        <li class="nav-item align-self-center mr-3">
                            <?= EditButton::widget([
                                'options' => [
                                    'title' => 'Edit Contact',
                                ],
                                'url' => [
                                    'contact/update',
                                    'id' => $contact->id,
                                    'linkUserId' => $user ? $user->id : null,
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
                                    <th class="align-middle"><?= Yii::t('app', 'Groups') ?></th>
                                    <td class="align-middle">
                                        <?php foreach ($contact->getGroups()->each() as $group) : ?>
                                            <?= Html::badge('primary', $group->name) . '&nbsp'; ?>
                                        <?php endforeach; ?>
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
                                                'linkUserId' => $user ? $user->id : null,
                                            ],
                                        ]) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= $contact->getAttributeLabel('is_real'); ?></th>
                                    <td class="align-middle"><?= $contact->getIsRealBadge(); ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= $contact->getAttributeLabel('relation'); ?></th>
                                    <td class="align-middle"><?= $contact->getRelationBadge(); ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= $contact->getAttributeLabel('is_basic_income_candidate'); ?></th>
                                    <td class="align-middle"><?= $contact->getIsBasicincomeCandidateLabel(); ?></td>
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

<?php if ($contact->isUser() && $contact->hasDebtTransferPriority()) : ?>
<div class="debt-limits-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('app', 'Debt transfer limits'); ?></h3>
                    <div class="card-tools">
                        <?= ModalAjax::widget([
                            'id' => 'add-debt-transfer-limit',
                            'header' => Yii::t('app', 'Add debt transfer limit'),
                            'toggleButton' => [
                                'label' => Html::icon('add'),
                                'title' => Yii::t('app', 'Add'),
                                'class' => 'btn btn-outline-success',
                                'style' =>  [
                                    'float' => 'right',
                                ],
                            ],
                            'url' => Url::to([
                                'contact/add-debt-transfer-limit',
                                'linkUserId' => $contact->link_user_id,
                            ]),
                        ]);?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        //'filterModel' => $searchModel,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            [
                                'attribute' => 'currency_id',
                                'value' => function ($model) {
                                    return $model->currency->code;
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'max_amount',
                                'value' => function ($model) {
                                    return $model->max_amount ?? '∞';
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'value' => function ($model) {
                                    return ModalAjax::widget([
                                        'id' => 'change-debt-transfer-limit-' . $model->id,
                                        'header' => Yii::t('app', 'Edit debt transfer limit'),
                                        'toggleButton' => [
                                            'label' => Html::icon('edit'),
                                            'title' => Yii::t('app', 'Edit'),
                                            'class' => 'btn btn-light edit-btn',
                                            'style' =>  [
                                                'float' => 'right',
                                            ],
                                        ],
                                        'url' => Url::to([
                                            'contact/change-debt-transfer-limit',
                                            'id' => $model->id,
                                        ]),
                                    ]);
                                },
                                'format' => 'raw',
                            ],
                        ],
                        'layout' => $dataProvider->getCount() ? "{summary}\n{items}\n<div class='card-footer clearfix'>{pager}</div>" : '',
                        'pager' => [
                            'options' => [
                                'class' => 'pagination float-right',
                            ],
                            'linkContainerOptions' => [
                                'class' => 'page-item',
                            ],
                            'linkOptions' => [
                                'class' => 'page-link',
                            ],
                            'maxButtonCount' => 5,
                            'disabledListItemSubTagOptions' => [
                                'tag' => 'a',
                                'class' => 'page-link',
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
