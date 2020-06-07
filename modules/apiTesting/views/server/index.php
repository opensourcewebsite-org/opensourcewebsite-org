<?php

use app\modules\apiTesting\models\ApiTestServer;
use app\widgets\buttons\AddButton;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\apiTesting\models\ApiTestProjectSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $project \app\modules\apiTesting\models\ApiTestProject */
$this->title = "Servers";
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['/apiTesting/project']];
$this->params['breadcrumbs'][] = ['label' => $project->name.' testing', 'url' => ['/apiTesting/project/testing', 'id' => $project->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="api-test-server-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item align-self-center mr-4">
                            <?= AddButton::widget([
                                'url' => ['create', 'id' => $project->id],
                                'options' => [
                                    'title' => 'New Server',
                                ]
                            ]); ?>
                        </li>
                        <li class="nav-item align-self-center mr-4">
                            <?= \app\modules\apiTesting\widgets\ProjectDropdownMenu::widget([
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
                            'fullAddress',
                            [
                                'attribute' => 'status',
                                'format' => 'html',
                                'value' => function (ApiTestServer $server) {
                                    return $server->status == 0 ? '<span class="badge badge-dark">Domain Verification in progress</span>' : '<span class="badge badge-success">Verified</span>';
                                }
                            ],
                            [
                                'header' => '',
                                'format' => 'raw',
                                'value' => function (ApiTestServer $server) {
                                    return \yii\helpers\Html::a('<i class="fas fa-pen"></i>', ['update', 'id' => $server->id]);
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
