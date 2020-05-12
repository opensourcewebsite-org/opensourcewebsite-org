<?php

use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<div class="profile-form">
    <?php
    $genderForm = ActiveForm::begin();
    ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <h3 class="card-title p-3">
                        <?= Yii::t('app', 'Edit gender'); ?>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $genderForm->field($user, 'gender_id')->widget(
                                Select2::class,
                                [
                                    'name'    => 'change-gender',
                                    'value'   => Yii::$app->user->identity->gender ?? '',
                                    'data'    => $genders,
                                    'options' => [
                                        'id'     => 'gender-value',
                                        'prompt' => '',
                                    ]
                                ])->label(Yii::t('app', 'Gender')); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?= CancelButton::widget([
                        'url' => ['/account']
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
