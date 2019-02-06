<?php

use yii\helpers\Html;
use \yii\widgets\Pjax;

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
    'id' => 'replyForm' . $parent,
    'enablePushState' => false,
    'timeout' => 99999999,
    'clientOptions' => ['container' => $container]
];

Pjax::begin($options);

$modelInst = new $modelClass;
$shortName = $modelInst->formName();

echo
    Html::beginForm(['/comment/default/handler'], 'post', ['data-pjax' => true, 'class' => 'formReplies']) .
    Html::hiddenInput('related', $related) .
    Html::hiddenInput('model', $modelClass) .
    Html::hiddenInput('material', $material) .
    Html::hiddenInput('mainForm', $mainForm) .
    Html::hiddenInput($shortName . '[parent_id]', $parent) .
    Html::img(
        'https://secure.gravatar.com/avatar/b4284e48ee666373b3e7adee3cbd0958?r=g&amp;s=20',
        ['class' => 'img-fluid img-circle img-sm']
    ) .
    Html::tag(
        'div',
        Html::activeTextarea(
            $modelInst,
            'message',
            [
                'rows'        => 3,
                'class'       => 'form-control',
                'placeholder' => 'Add a public comment...',
            ]
        ) .
        Html::tag(
            'div',
            ($mainForm ? '' :
                Html::button(
                    'CANCEL',
                    [
                        'class' => 'btn btn-light mr-2',
                        'data-toggle' => 'collapse',
                        'data-target' => '#collapse' . $parent,
                    ]
                )
            ) .
            Html::submitButton('COMMENT', ['class' => 'btn btn-secondary']),
            ['class' => 'mt-2 float-right']
        ),
        ['class' => 'img-push']
    ) .
    Html::endForm();

Pjax::end();
