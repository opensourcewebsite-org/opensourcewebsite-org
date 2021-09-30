<?php

use app\widgets\buttons\SaveButton;
use app\widgets\buttons\CancelButton;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'enableAjaxValidation' => true,
]); ?>
<div class="form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $form->field($settingValue, 'value')->textInput()->label(false); ?>
                            <?= Html::hiddenInput('setting_key', $setting_key); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <?= SaveButton::widget([
                    'text' => Yii::t('app', 'Add'),
                    'options' => [
                        'title' => Yii::t('app', 'Add'),
                    ],
                ]); ?>
                <?= CancelButton::widget(); ?>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
