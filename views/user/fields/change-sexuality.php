<?php

use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<div class="profile-form">
    <?php
    $sexualityForm = ActiveForm::begin();
    ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <h3 class="card-title p-3">
                        <?= Yii::t('app', 'Edit sexuality'); ?>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $sexualityForm->field($user, 'sexuality_id')->widget(
                                Select2::class,
                                [
                                    'name'    => 'change-sexuality',
                                    'value'   => Yii::$app->user->identity->sexuality ?? '',
                                    'data'    => $sexualities,
                                    'options' => [
                                        'id'     => 'sexuality-value',
                                        'prompt' => '',
                                    ]
                                ])->label(Yii::t('app', 'Sexuality')); ?>
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
