<?php

use app\widgets\DebtDistributionSettings\DebtRedistributionSettings;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use app\widgets\buttons\SaveButton;
use app\widgets\buttons\CancelButton;
use yii\widgets\Pjax;

/* @var $this    yii\web\View */
/* @var $context DebtRedistributionSettings */
/* @var $form    ActiveForm */
/* @var $header  string */

$context = $this->context;
$model   = $context->debtRed;
?>

<?php Pjax::begin(); ?>

<?php $form = ActiveForm::begin([
    //we need to specify ID for each widget, which is loaded via AJAX.
    //Else - if ID will be generated - we may have duplicated IDs in HTML page.
    'id'      => 'active-form-debt-redistribution',
    'action'  => ['/debt-redistribution/save', 'id' => $model->id],
    'options' => ['class' => 'debt-redistribution-form'],
]); ?>

    <?= $form->errorSummary($model) ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->hiddenInput() ?>
    <?= $form->field($model, 'contactId', ['template' => '{input}'])->hiddenInput() ?>

    <div class="row">
        <div class="col-4">
            <?= $form->field($model, 'currency_id')->widget(Select2::class, [
                'data'    => $context->currencyList,
                'options' => [
                    'prompt' => '',
                ],
            ]); ?>
        </div>
        <div class="col-8">
            <?= $form->field($model, 'max_amount')->textInput([
                'type'        => 'number',
                'placeholder' => Yii::t('app', 'No limit'),
            ]); ?>
        </div>
    </div>
    <?= SaveButton::widget([
        'options' => ['data-pjax' => 0]
    ]); ?>
    <?= CancelButton::widget(); ?>
    <button type="submit" style="display:none;"></button><!-- to allow to submit form on `enter` key -->
<?php ActiveForm::end() ?>
<?php Pjax::end(); ?>

<div id="headerForModal" style="display:none;"><h5 class="modal-title"><?=$header?></h5></div>
