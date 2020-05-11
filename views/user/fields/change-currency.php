<?php

use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<div class="profile-form">
    <?php
    $currencyForm = ActiveForm::begin();
    ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <h3 class="card-title p-3">
                        <?= Yii::t('app', 'Edit currency'); ?>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $currencyForm->field($user, 'currency_id')->widget(
                                Select2::class,
                                [
                                    'name'    => 'change-currency',
                                    'value'   => Yii::$app->user->identity->currency ?? '',
                                    'data'    => $currencies,
                                    'options' => [
                                        'id'     => 'currency-value',
                                        'prompt' => '',
                                    ]
                                ])->label(Yii::t('app', 'Currency')); ?>
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
