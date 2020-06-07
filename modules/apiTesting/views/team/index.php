<?php

use app\modules\apiTesting\models\ApiTestTeam;
use app\widgets\buttons\AddButton;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\apiTesting\models\ApiTestTeamSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $project \app\modules\apiTesting\models\ApiTestProject */

$this->title = $project->name.' team';
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['/apiTesting/project']];
$this->params['breadcrumbs'][] = ['label' => $project->name, 'url' => ['/apiTesting/project/update', 'id' => $project->id]];
$this->params['breadcrumbs'][] = 'Team';
?>
<?=$this->render('../common/_project_tab', [
    'model' => $project
]); ?>
<div class="api-test-team-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item align-self-center mr-4">
                            <?= AddButton::widget([
                                'url' => ['invite', 'project_id' => $project->id],
                                'options' => [
                                    'title' => 'New Project',
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
                            [
                                'format' => 'raw',
                                'value' => function (ApiTestTeam $model) {
                                    return $this->render('_member_column', [
                                        'model' => $model
                                    ]);
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
