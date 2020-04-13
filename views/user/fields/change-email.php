<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<div class="profile-form">
    <?php
    $emailForm = ActiveForm::begin();
    ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <h3 class="card-title p-3">
                        <?= Yii::t('app', 'Edit email') ?>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $emailForm->field($emailModel, 'email')->input('email', ['value' =>
                                Yii::$app->user->identity->email])->label('Email'); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
                    <?= Html::a(Yii::t('app', 'Cancel'), ['/account'], [
                        'class' => 'btn btn-secondary',
                        'title' => Yii::t('app', 'Cancel'),
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
