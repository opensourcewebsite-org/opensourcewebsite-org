<?php

use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\apiTesting\models\ApiTestResponseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Api Test Responses';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="api-test-response-index">

    <h1><?= Html::encode($this->title); ?></h1>

    <p>
        <?= Html::a('Create Api Test Response', ['create'], ['class' => 'btn btn-success']); ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]);?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'request_id',
            'headers:ntext',
            'body:ntext',
            'cookies:ntext',
            //'code',
            //'time:datetime',
            //'size',
            //'created_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
