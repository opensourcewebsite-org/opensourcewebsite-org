<?php

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;

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

    Pjax::begin(['id' => 'updateForm' . $model->id, 'enablePushState' => false,
        'clientOptions' => ['container' => '#textMessage' . $model->id]]);

    echo
        Html::beginForm(['/comment/default/update', 'id' => $model->id], 'post', ['data-pjax' => true]) .
        Html::hiddenInput('model', $modelClass) .
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
                Html::submitButton('UPDATE', ['class' => 'btn btn-secondary']),
                ['class' => 'mt-2 float-right']
            ),
            ['class' => 'img-push']
        ) .
        Html::endForm();

    Pjax::end();
    Modal::end();
