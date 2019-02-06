<?php

use yii\widgets\Pjax;
use yii\helpers\Html;

/**
 * @var $this \yii\web\View
 * @var $pagination \yii\data\Pagination
 * @var array $items
 * @var $model
 * @var $related
 * @var $material
 */

$comment = [];
$nextPage = Yii::$app->request->get('page', 1) + 1;

foreach ($items as $step => $item) {
    $options = [
        'item'     => $item,
        'model'    => $model,
        'related'  => $related,
        'material' => $material,
        'level'    => 1,
    ];

    $comment[] = $this->render('_comment_template', $options);
}

echo implode("\n", $comment);


Pjax::begin([
    'id'              => 'comment_wrapper' . $nextPage,
    'enablePushState' => false,
    'timeout'         => 99999999,
    'clientOptions'   => [
        'container' => '#next-page' . $nextPage,

    ],
    'formSelector'    => false,
]);

if ($pagination->pageCount != 0 && Yii::$app->request->get('page', 1) < $pagination->pageCount) {
    echo '<div class=\'text-center show-more py-3\'>';
    echo Html::a('SHOW MORE REPLIES', [
        '/comment/default/pager',
        'material' => $material,
        'page'     => $nextPage,
        'model'    => $model,
        'related'  => $related,
    ]);
    echo '</div>';

    $this->registerJs('
    $(\'#next-page' . $nextPage . '\').on(\'pjax:end\', function(event) {
            $(\'#comment_wrapper' . $nextPage . '\').remove();
            $(\'#next-page' . $nextPage . '\').addClass(\'card-comment\');
        });');
}
Pjax::end();
