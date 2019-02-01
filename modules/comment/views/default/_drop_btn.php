<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/**
 * @var $item
 * @var $model
 */

Pjax::begin([
    'id'              => 'dropbtn' . $item->id,
    'enablePushState' => false,
    'timeout'         => 999999999 * 999999,
    'formSelector'    => false,
    'clientOptions'   => ['container' => '#comments'],
    'options'         => [
        'class' => 'float-right',
    ],
]);

echo Html::tag(
    'a',
    '<i class="fas fa-trash mx-1"></i>',
    [
        'href' => \yii\helpers\Url::to(['/comment/default/delete', 'model'    => $model, 'id' => $item->id,
                                                                   'material' => $material, 'related' => $related,
        ]),
    ]
);

Pjax::end();
