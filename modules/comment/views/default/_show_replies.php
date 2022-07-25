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
    'timeout' => 99999999,
    'formSelector' => false,
    'linkSelector' => '#repliesLink' . $inside
]);

echo (($count > 0) ? Html::tag(
    'a',
    "View replies ({$count})",
    [
        'class'         => 'text-muted show-reply',
        'href'          => Url::to(['/comment/default/index', 'parent_id' => $inside, 'material' => $material, 'related' => $related, 'model' => $model]),
        'id' => 'repliesLink' . $inside
    ]
) : '') . Html::tag('div', '', ['id' => 'collapseReply' . $inside]);

Pjax::end();
