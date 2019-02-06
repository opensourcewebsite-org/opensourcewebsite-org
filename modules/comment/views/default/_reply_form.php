<?php

use yii\helpers\Html;
use \yii\widgets\Pjax;
use yii\widgets\ActiveForm;

/**
 * @var $modelClass
 * @var $parent int
 * @var $related string
 * @var $material string
 * @var $mainForm bool
 */

$container = '#main-response';
if ($parent) {
    $container = '#insideComments' . $parent;
}

$options = [
    'id'              => 'replyForm' . $parent,
    'enablePushState' => false,
    'timeout'         => 99999999,
    'clientOptions'   => ['container' => $container],
];

Pjax::begin($options);

$modelInst = new $modelClass;
$shortName = $modelInst->formName();

$form = ActiveForm::begin([
    'action'  => ['/comment/default/handler'],
    'options' => [
        'id' => 'activeReplyForm' . $parent,
        'data-pjax' => true,
        'class' => 'formReplies',
    ],
]);
?>

<?= Html::hiddenInput('related', $related) .
Html::hiddenInput('model', $modelClass) .
Html::hiddenInput('material', $material) .
Html::hiddenInput('mainForm', $mainForm) .
Html::hiddenInput($shortName . '[parent_id]', $parent) ?>

<?= Html::img(
    'https://secure.gravatar.com/avatar/b4284e48ee666373b3e7adee3cbd0958?r=g&amp;s=20',
    ['class' => 'img-fluid img-circle img-sm']
) ?>

    <div class="img-push">
        <?= $form->field($modelInst, 'message')->textarea([
            'rows'        => 3,
            'class'       => 'form-control',
            'placeholder' => 'Add a public comment...',
        ])->label(false) ?>
        <div class="mt-2 float-right">
            <?= ($mainForm
                ? ''
                :
                Html::button(
                    'CANCEL',
                    [
                        'class'       => 'btn btn-light mr-2',
                        'data-toggle' => 'collapse',
                        'data-target' => '#collapse' . $parent,
                    ]
                )
            ) .
            Html::submitButton('COMMENT', ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

<?php
ActiveForm::end();

Pjax::end();
