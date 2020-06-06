<?php

use app\modules\apiTesting\models\ApiTestProject as ApiTestProject;
use app\modules\apiTesting\models\ApiTestRunner;
use app\widgets\buttons\AddButton;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\apiTesting\models\ApiTestRunnerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Runner';
$this->params['breadcrumbs'][] = ['label' => $project->name.' testing', 'url' => ['/apiTesting/project/testing', 'id' => $project->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="api-test-runner-index">
    <?=$this->render('_tabs', [
        'project' => $project
    ]); ?>
    <?=$this->render('_grid', [
        'dataProvider' => $dataProvider
    ]); ?>
</div>
