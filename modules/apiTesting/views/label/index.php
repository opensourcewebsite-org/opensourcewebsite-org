<?php

use app\components\helpers\Icon;
use app\modules\apiTesting\widgets\ProjectDropdownMenu;
use app\widgets\buttons\AddButton;
use app\widgets\Modal;
use app\widgets\ModalAjax;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\apiTesting\models\ApiTestLabelController */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $project \app\modules\apiTesting\models\ApiTestProject */

$this->title = 'Labels';
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['/apiTesting/project']];
$this->params['breadcrumbs'][] = ['label' => $project->name.' testing', 'url' => ['/apiTesting/project/testing', 'id' => $project->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="api-test-label-index">
    <div class="api-test-server-index">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex p-0">
                        <ul class="nav nav-pills ml-auto p-2">
                            <li class="nav-item align-self-center mr-4">
                                <?= ModalAjax::widget([
                                    'id' => 'add-wikinews',
                                    'header' => Yii::t('user', 'Add label'),
                                    'closeButton' => false,
                                    'toggleButton' => [
                                        'label' => Icon::ADD,
                                        'class' => 'btn btn-outline-success',
                                        'style' => ['float' => 'right'],
                                    ],
                                    'url' => Url::to(['create', 'id' => $project->id]),
                                    'ajaxSubmit' => true,
                                ]); ?>
                            </li>
                            <li>
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
                                'name',
                                [
                                    'format' => 'raw',
                                    'value' => function (\app\modules\apiTesting\models\ApiTestLabel $label) {
                                        return ModalAjax::widget([
                                            'id' => 'update-label-'.$label->id,
                                            'header' => Yii::t('user', 'Update label'),
                                            'closeButton' => false,
                                            'toggleButton' => [
                                                'label' => Icon::EDIT,
                                                'class' => 'btn btn-outline-success',
                                                'style' => ['float' => 'right'],
                                            ],
                                            'url' => Url::to(['update', 'id' => $label->id]),
                                            'ajaxSubmit' => true,
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



</div>
