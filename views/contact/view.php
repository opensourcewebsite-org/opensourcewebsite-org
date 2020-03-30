<?php

use app\widgets\DebtDistributionSettings\DebtRedistributionSettings;
use yii\helpers\Html;
use app\models\Contact;
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
                                <?= DebtRedistributionSettings::widget(['contact' => $model]); ?>
                            </li>
                        <?php } ?>
                        <li class="nav-item align-self-center mr-4">
                            <?= Html::a('<i class="fa fa-edit"></i>', ['contact/update', 'id' => $model->id], [
                                'class' => 'btn btn-light',
                                'title' => Yii::t('app', 'Edit Contact'),
                            ]); ?>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'label' => 'User ID / Username',
                                'value' => static function (Contact $model) {
                                    if (!empty($model->linkedUser)) {
                                        return !empty($model->linkedUser->username) ? '@' . $model->linkedUser->username : '#' . $model->linkedUser->id;
                                    }
                                },
                            ],
                            'name',
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
