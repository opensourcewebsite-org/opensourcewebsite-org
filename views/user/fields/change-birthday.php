<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;


?>
<div class="profile-form">
    <?php
    $birthdayForm = ActiveForm::begin();
    ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <h3 class="card-title p-3">
                        <?= Yii::t('app', 'Edit birthday') ?>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $birthdayForm->field($user, 'birthday')->widget(DatePicker::class, [
                                'name'          => 'birthday',
                                'id'            => 'birthday-value',
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format'    => 'mm/dd/yyyy',
                                ],
                            ])->label('Birthday'); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
                    <?= Html::a(Yii::t('app', 'Cancel'), ['/account'],
                        [
                            'class' => 'btn btn-secondary',
                            'title' => Yii::t('app', 'Cancel')
                        ]); ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
