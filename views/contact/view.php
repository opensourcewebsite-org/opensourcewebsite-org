<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Contact */

$this->title = Yii::t('app', 'View Contact');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contacts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = '#' . $model->id;

?>
<div class="contact-view">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
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
                                'value' => !empty($model->user->username) ? $model->user->username : '#' . $model->user->id,
                            ],
                            'name',
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
