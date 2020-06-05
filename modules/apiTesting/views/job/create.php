<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\apiTesting\models\ApiTestJob */

$this->title = 'Create Job';
$this->params['breadcrumbs'][] = ['label' => $project->name.' testing', 'url' => ['index', 'id' => $project->id]];
$this->params['breadcrumbs'][] = ['label' => 'Jobs', 'url' => ['index', 'id' => $project->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="api-test-job-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>
</div>
