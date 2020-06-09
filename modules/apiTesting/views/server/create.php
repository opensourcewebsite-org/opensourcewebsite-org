<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\apiTesting\models\ApiTestServer */

$this->title = 'Create Server';
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['/apiTesting/project']];
$this->params['breadcrumbs'][] = ['label' => $project->name.' testing', 'url' => ['/apiTesting/project/testing', 'id' => $project->id]];
$this->params['breadcrumbs'][] = ['label' => 'Servers', 'url' => ['index', 'id' => $project->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="api-test-server-create">

    <?= $this->render('_form', [
        'model' => $model,
        'project' => $project,
    ]); ?>

</div>
