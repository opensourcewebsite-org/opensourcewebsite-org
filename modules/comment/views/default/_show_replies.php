<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/**
 * @var $inside int
 * @var $count int
 * @var $related string
 * @var $model string
 * @var $material int
 */

Pjax::begin([
    'id' => 'insideComments' . $inside,
    'enablePushState' => false,
    'timeout' => 999999999 * 999999,
    'formSelector' => false,
//    'clientOptions' => ['container' => '#collapseReply' . $inside]
]);

echo (($count > 0) ? Html::tag(
    'a',
    "replies ({$count})",
    [
        'class'         => 'text-muted show-reply',
        'href'          => Url::to(['/comment/default/index', 'parent_id' => $inside, 'material' => $material, 'related' => $related, 'model' => $model]),
    ]
) : '') . Html::tag('div', '', ['id' => 'collapseReply' . $inside]);

Pjax::end();
