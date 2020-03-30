<?php

use app\models\DebtRedistributionForm;
use app\widgets\Modal;
use yii\widgets\ActiveForm;

/* @var $this    yii\web\View */
/* @var $context \app\widgets\DebtDistributionSettings\DebtRedistributionSettings */
/* @var $form    ActiveForm */
/* @var $header  string */
/* @var $footer  string */

$context = $this->context;

$model = new DebtRedistributionForm();
$model->loadContact($context->contact);
?>

<?php $form = ActiveForm::begin([
    'action'  => ['/debt-redistribution/save', 'id' => $model->id],
    'options' => ['class' => 'debt-redistribution-form'],
]); ?>
    <?php Modal::begin([
        'header'        => $header,
        'footer'        => $footer,
        'footerOptions' => ['class' => 'card-footer'],
        'toggleButton'  => [
            'label' => Yii::t('app', 'Debt Redistribution'),
            'class' => 'btn btn-light',
        ],
    ]); ?>

        <?= $form->errorSummary($model) ?>

        <?= $form->field($model, 'contactId', ['template' => '{input}'])->hiddenInput() ?>
        <div class="row">
            <div class="col">
                <?= $form->field($model, 'max_amount')->textInput(['type' => 'number']); ?>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <?= $form->field($model, 'priority')->textInput(['type' => 'number']); ?>
            </div>
        </div>

    <?php Modal::end() ?>

<?php ActiveForm::end() ?>

<?php
$this->registerJs(<<<JS
$('#$form->id').on('beforeSubmit', function () {
    var yiiForm = $(this);
    
    $.ajax({
        type: yiiForm.attr('method'),
        url: yiiForm.attr('action'),
        data: yiiForm.serializeArray(),
        success: function(data, textStatus, jqXHR) {
            if(data.success) {
                window.location.reload();
            } else if (data.validation) {
                yiiForm.yiiActiveForm('updateMessages', data.validation, true);
            } else {
                let errTitle = 'Incorrect server response. Reload page, please.';
                alert(errTitle);
                console.log(errTitle, jqXHR.responseJSON ? jqXHR.responseJSON : jqXHR.responseText);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            let errTitle = textStatus + ': ' + errorThrown + '. Reload page, please.';
            alert(errTitle);
            console.log(errTitle, jqXHR.responseJSON ? jqXHR.responseJSON : jqXHR.responseText);
        },
    });

    return false;
});
JS
);
