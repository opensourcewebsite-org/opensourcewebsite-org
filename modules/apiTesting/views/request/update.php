<?php

use app\assets\AceEditorAsset;

/* @var $this yii\web\View */
/* @var $model app\modules\apiTesting\models\ApiTestRequest */
use yii\helpers\Html;

AceEditorAsset::register($this);

$this->title = 'Update Api Test Request: '.$model->name;
$this->params['breadcrumbs'][] = ['url' => ['/apiTesting/project/testing', 'id' => $model->server->project->id], 'label' => $model->server->project->name.' testing'];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="api-test-request-update">
    <?= $this->render('_form', [
        'model' => $model,
        'project' => $model->server->project
    ]); ?>
</div>
