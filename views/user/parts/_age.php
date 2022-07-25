<?php
use yii\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\widgets\Pjax;

/**
 * @var $dataProvider ArrayDataProvider
 * @var $id integer
 */
Pjax::begin([
    'id' => 'age'
]);
echo GridView::widget([
    'id' => 'age',
    'dataProvider' => $dataProvider,
    'layout' => "{items}</div></div><div class='card-footer clearfix'></div>",
    'tableOptions' => ['class' => 'table table-condensed table-hover'],
]);
Pjax::end();
