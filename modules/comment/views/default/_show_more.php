<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

Pjax::begin([
    'id'              => 'paginationReplies_' . $parent_id . '_' . $inside,
    'enablePushState' => false,
    'timeout'         => 99999999,
    'formSelector'    => false,
    'linkSelector'    => '#moreReplies_' . $parent_id . '_' . $inside,
    'clientOptions'   => ['container' => '#inside-next-page_' . $parent_id . '_' . $inside],
]);

echo Html::a(
    'more comments',
    [
        'inside-pager',
        'parent_id' => $parent_id,
        'material'  => $material,
        'related'   => $related,
        'model'     => $model,
        'page'      => $inside,
    ],
    [
        'id' => 'moreReplies_' . $parent_id . '_' . $inside,
    ]
);

$this->registerJs('
    $(\'#inside-next-page_' . $parent_id . '_' . $inside . '\').on(\'pjax:end\', function(event) {
            $(\'#moreReplies_' . $parent_id . '_' . $inside . '\').remove();
        });');

Pjax::end();
