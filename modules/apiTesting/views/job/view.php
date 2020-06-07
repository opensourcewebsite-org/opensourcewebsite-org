<?php

use app\widgets\buttons\AddButton;
use app\widgets\buttons\EditButton;
use yii\bootstrap4\Tabs;
use yii\data\ActiveDataProvider;

/* @var $this yii\web\View */
/* @var $model app\modules\apiTesting\models\ApiTestJob */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => $model->project->name.' testing', 'url' => ['/apiTesting/project/testing', 'id' => $model->project->id]];
$this->params['breadcrumbs'][] = ['label' => 'Jobs', 'url' => ['index', 'id' => $model->project->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="api-test-job-view">
    <div class="row">
        <div  class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <?=\app\modules\apiTesting\widgets\ProjectDropdownMenu::widget([
                        'project' => $model->project
                    ]); ?>
                    <?= AddButton::widget([
                        'text' => 'Run',
                        'url' => ['run', 'id' => $model->id],
                        'options' => [
                            'style' => [
                                'float' => 'right'
                            ]
                        ]
                    ]); ?>

                    <?= EditButton::widget([
                        'url' => ['update', 'id' => $model->id],
                        'options' => [
                            'class' => 'btn btn-outline-primary mr-2',
                            'style' => [
                                'float' => 'right',

                            ]
                        ]
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <?= Tabs::widget([
                'items' => [
                    [
                        'label' => 'Requests',
                        'content' => $this->render('_tabs/_requests', [
                            'dataProvider' => new ActiveDataProvider([
                                'query' => $model->getRequests()
                            ]),
                            'job' => $model
                        ]),
                        'active' => true,
                    ],
                    [
                        'label' => 'Schedules',
                        'content' => $this->render('_tabs/_schedules', [
                            'dataProvider' => new ActiveDataProvider([
                                'query' => $model->getSchedules()
                            ]),
                            'job' => $model
                        ]),
                    ]
                ]
            ]); ?>
        </div>
    </div>

</div>
