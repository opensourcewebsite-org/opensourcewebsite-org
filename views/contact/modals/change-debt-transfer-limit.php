<?php

use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use app\widgets\buttons\DeleteButton;
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
                                ->widget(CurrencySelect::class, [
                                    'options' => [
                                        'disabled' => true,
                                    ],
                                ]); ?>
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
                <?= SaveButton::widget(); ?>
                <?= CancelButton::widget() ?>
                <?= DeleteButton::widget([
                    'url' => [
                        '/contact/delete-debt-transfer-limit',
                        'id' => $model->id,
                    ],
                ]);?>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
