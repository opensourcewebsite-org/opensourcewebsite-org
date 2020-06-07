<?php

use app\modules\apiTesting\models\ApiTestJob as ApiTestJob;
use app\modules\apiTesting\models\ApiTestServer;
use app\modules\apiTesting\widgets\ProjectDropdownMenu;
use app\widgets\buttons\AddButton;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Html as HtmlAlias;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\apiTesting\models\ApiTestJobSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $project \app\modules\apiTesting\models\ApiTestProject */

$this->title = 'Jobs';
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['/apiTesting/projects']];
$this->params['breadcrumbs'][] = ['label' => $project->name.' testing', 'url' => ['/apiTesting/project/testing', 'id' => $project->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="api-test-job-index">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item align-self-center mr-4">
                            <?= AddButton::widget([
                                'url' => ['create', 'id' => $project->id],
                                'options' => [
                                    'title' => 'New Job',
                                ]
                            ]); ?>
                        </li>
                        <li class="nav-item align-self-center mr-4">
                            <?= ProjectDropdownMenu::widget([
                                'project' => $project
                            ]); ?>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            'id',
                            [
                                'attribute' => 'name',
                                'format' => 'html',
                                'value' => function (ApiTestJob $job) {
                                    return Html::a($job->name, ['view', 'id' => $job->id]);
                                }
                            ],
                            [
                                'header' => 'Requests',
                                'value' => function (ApiTestJob $job) {
                                    return $job->getRequests()->count();
                                }
                            ],
                            [
                                'header' => 'Tested at',
                                'value' => function (ApiTestJob $job) {
                                    /** @var \app\modules\apiTesting\models\ApiTestRunner $latestRun */
                                    if ($latestRun = $job->getRunners()->orderBy('id DESC')->one()) {
                                        return Yii::$app->formatter->asRelativeTime($latestRun->timing);
                                    }
                                }
                            ],
                            [
                                'header' => 'Test result',
                                'format' => 'raw',
                                'value' => function (ApiTestJob $job) {
                                    /** @var \app\modules\apiTesting\models\ApiTestRunner $latestRun */
                                    if ($latestRun = $job->getRunners()->orderBy('id DESC')->one()) {
                                        return Html::tag('span', $latestRun->getStatusLabel(), ['class' => 'badge badge-'.$latestRun->getStatusColorClass()]);
                                    }
                                }
                            ],
                            [
                                'format' => 'raw',
                                'value' => function (ApiTestJob $job) {
                                    return Html::a('Run', ['run', 'id' => $job->id], ['class' => 'btn btn btn-outline-success float-right']);
                                }
                            ]
                        ],
                        'layout' => "{summary}\n{items}\n<div class='card-footer clearfix'>{pager}</div>",
                        'pager' => [
                            'options' => [
                                'class' => 'pagination float-right',
                            ],
                            'linkContainerOptions' => [
                                'class' => 'page-item',
                            ],
                            'linkOptions' => [
                                'class' => 'page-link',
                            ],
                            'maxButtonCount' => 5,
                            'disabledListItemSubTagOptions' => [
                                'tag' => 'a',
                                'class' => 'page-link',
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
