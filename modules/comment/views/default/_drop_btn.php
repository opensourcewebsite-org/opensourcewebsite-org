<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/**
 * @var $item
 * @var $model
 * @var $level
 */

$parent = null;
if ($level > 1) {
    $getParent = $model::findOne(['id' => $item->id]);
    $parent = $getParent->parent_id;
}

//TODO Optimization
if ($parent) {
    Pjax::begin([
        'id'              => 'dropbtn' . $item->id,
        'enablePushState' => false,
        'timeout'         => 99999999,
        'formSelector'    => false,
        'clientOptions'   => ['container' => '#insideComments' . $parent],
        'options'         => [
            'class' => 'float-right',
        ],
    ]);
} else {
    Pjax::begin([
        'id'              => 'dropbtn' . $item->id,
        'enablePushState' => false,
        'timeout'         => 999999999 * 999999,
        'formSelector'    => false,
        'clientOptions'   => ['container' => '#comment' . $item->id],
        'options'         => [
            'class' => 'float-right',
        ],
    ]);

    $this->registerJs('
        $(\'#comment' . $item->id . '\').on(\'click\', \'#dropbtn' . $item->id . '\', function() {
            $(\'#comment' . $item->id . '\').on(\'pjax:end\', function(event) {
                $(\'#comment' . $item->id . '\').remove(); 
            });
        });
    ');
}

echo Html::tag(
    'a',
    '<i class="fas fa-trash mx-1"></i>',
    [
        'href' => \yii\helpers\Url::to([
            '/comment/default/delete',
            'model'    => $model,
            'id'       => $item->id,
            'material' => $material,
            'related'  => $related,
            'level'    => $level,
        ]),
        'onclick'=> 'var r = confirm(\'Are you sure?\'); if (r == true) {return true;} else {return false;}'
    ]
);

Pjax::end();
