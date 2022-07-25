<?php

use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use app\widgets\selects\CurrencySelect;
use yii\widgets\ActiveForm;
use yii\helpers\Html;

$form = ActiveForm::begin([
    'enableAjaxValidation' => true,
]);
?>
<div class="form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'currency_id')
                                ->widget(CurrencySelect::class); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'max_amount')
                                ->textInput([
                                    'placeholder' => '∞',
                                ]); ?>
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
                <?= CancelButton::widget() ?>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
