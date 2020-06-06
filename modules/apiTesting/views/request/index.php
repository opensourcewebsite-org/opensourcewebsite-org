<?php

use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\apiTesting\models\ApiTestRequestSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Api Test Requests';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="api-test-request-index">

    <h1><?= Html::encode($this->title); ?></h1>

    <p>
        <?= Html::a('Create Api Test Request', ['create'], ['class' => 'btn btn-success']); ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]);?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'server_id',
            'name',
            'method',
            'uri',
            //'body:ntext',
            //'correct_response_code',
            //'updated_at',
            //'updated_by',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
