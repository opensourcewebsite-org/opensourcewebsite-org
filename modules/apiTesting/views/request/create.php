<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\apiTesting\models\ApiTestRequest */

$this->title = 'Create Api Test Request';
$this->params['breadcrumbs'][] = ['label' => 'Api Test Requests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="api-test-request-create">
    <?= $this->render('_form', [
        'model' => $model,
        'project' => $project
    ]); ?>
</div>
