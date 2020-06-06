<?php

use app\components\helpers\DebtHelper;
use app\models\Debt;
use app\modules\apiTesting\models\ApiTestProject as ApiTestProject;
use app\modules\apiTesting\widgets\ProjectDropdownMenu;
use app\widgets\buttons\AddButton;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\apiTesting\models\ApiTestProjectSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $projectQtyRate int */
$this->title = 'Projects';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="debt-index">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <b>Projects:</b> <?=Yii::$app->user->identity->getProjects()->count(); ?>/<?=Yii::$app->user->identity->maxProjectsCount; ?>
                (<?=$projectQtyRate; ?> per 1 user rating)
            </div>
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item align-self-center mr-4">
                            <?= AddButton::widget([
                                'url' => ['create'],
                                'options' => [
                                    'title' => 'New Project',
                                ]
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
                                'attribute' => 'name',
                                'format' => 'raw',
                                'value' => function (ApiTestProject $model) {
                                    return Html::a($model->name, ['/apiTesting/project/testing', 'id' => $model->id]);
                                }
                            ],
                            [
                                'format' => 'raw',
                                'value' => function (ApiTestProject $model) {
                                    return $this->render('_project_actions_button.php', [
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
