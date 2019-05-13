<?php

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

/**
 * @var $modelClass
 * @var $model
 * @var $parent int
 * @var $related string
 * @var $material string
 */

$shortName = $model->formName();

Modal::begin([
    'id'           => 'updateModal' . $model->id,
    'toggleButton' => false,
]);

Pjax::begin([
    'id'            => 'updateForm' . $model->id, 'enablePushState' => false,
    'clientOptions' => ['container' => '#textMessage' . $model->id],
]);


$form = ActiveForm::begin([
    'action'  => ['/comment/default/update', 'id' => $model->id],
    'options' => [
        'id' => 'activeUpdateForm' . $model->id,
        'data-pjax' => true,
    ],
]);
?>

<?= Html::hiddenInput('model', $modelClass) ?>

    <div class="img-push">
        <?= $form->field($model, 'message')->textarea([
            'rows'        => 3,
            'class'       => 'form-control',
            'placeholder' => 'Add a public comment...',
            'maxlength' => true
        ])->label(false) ?>
        <div class="mt-2 float-right">
            <?= Html::button(
                'CANCEL',
                [
                    'class'       => 'btn btn-light mr-2',
                    'data-toggle' => 'modal',
                    'data-target' => '#updateModal' . $model->id,
                ]
            ) .
            Html::submitButton('COMMENT', ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

<?php
ActiveForm::end();
Pjax::end();
Modal::end();
