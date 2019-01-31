<?php

use yii\helpers\Html;

/**
 * @var $model
 * @var $parent int
 */

$shortName = $model->formName();
$model->message = '';

echo
    Html::beginForm() .
    Html::hiddenInput($shortName . '[parent_id]', $parent) .
    Html::img(
        'https://secure.gravatar.com/avatar/b4284e48ee666373b3e7adee3cbd0958?r=g&amp;s=20',
        ['class' => 'img-fluid img-circle img-sm']
    ) .
    Html::tag(
        'div',
        Html::activeTextarea(
            $model,
            'message',
            [
                'rows'        => 3,
                'class'       => 'form-control',
                'placeholder' => 'Add a public comment...',
            ]
        ) .
        Html::tag(
            'div',
            Html::button('CANCEL', ['class' => 'btn btn-light mr-2']) .
            Html::submitButton('COMMENT', ['class' => 'btn btn-secondary']),
            ['class' => 'mt-2 float-right']
        ),
        ['class' => 'img-push']
    ) .
    Html::endForm();
