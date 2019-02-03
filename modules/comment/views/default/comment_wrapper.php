<?php

use yii\widgets\Pjax;
use yii\helpers\Html;

/**
 * @var $this \yii\web\View
 * @var array $items
 * @var $model
 * @var $related
 * @var $material
 */

$comment = [];
$nextPage = Yii::$app->request->get('page', 1) + 1;

foreach ($items as $item) {
    $comment[] = $this->render('_comment_template', [
        'item'     => $item,
        'model'    => $model,
        'related'  => $related,
        'material' => $material,
        'level'    => 1,
    ]);
}

echo implode("\n", $comment);


Pjax::begin([
    'id' => 'comment_wrapper' . $nextPage,
    'enablePushState' => false,
    'timeout' => 999999999 * 999999,
    'clientOptions' => ['container' => '#next-page' . $nextPage],
    'formSelector' => false,
]);

echo Html::a('page', [
        '/comment/default/pager',
        'material' => $material,
        'page' => $nextPage,
        'model' => $model,
        'related' => $related,
    ]
);
Pjax::end();
