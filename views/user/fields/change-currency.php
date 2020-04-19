<?php

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
                                Select2::class, [
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
                    <?= Html::submitButton(Yii::t('app', 'Save'), [
                            'class' => 'btn btn-success',
                            'title' => Yii::t('app', 'Save')]); ?>
                    <?= Html::a(Yii::t('app', 'Cancel'), ['/account'], [
                            'class' => 'btn btn-secondary',
                            'title' => Yii::t('app', 'Cancel')]); ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
