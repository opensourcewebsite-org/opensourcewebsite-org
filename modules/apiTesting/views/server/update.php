<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\apiTesting\models\ApiTestServer */

$this->title = 'Update Api Test Server: '.$model->id;
$this->params['breadcrumbs'][] = ['label' => 'Api Test Servers', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Api Test Servers', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="api-test-server-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
