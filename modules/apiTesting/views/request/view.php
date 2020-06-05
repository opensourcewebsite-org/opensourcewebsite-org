<?php

use app\modules\apiTesting\models\ApiTestResponse;
use app\modules\apiTesting\widgets\ProjectDropdownMenu;
use app\widgets\buttons\AddButton as AddButton;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\apiTesting\models\ApiTestRequest */

$this->title = $model->name.' request';
$this->params['breadcrumbs'][] = ['url' => ['/apiTesting/project/testing', 'id' => $model->server->project->id], 'label' => $model->server->project->name.' testing'];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="api-test-request-view">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'updated_at:datetime',
                            'updatedBy.name'
                        ]
                    ]); ?>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-12">
                    <?= ProjectDropdownMenu::widget([
                        'project' => $model->server->project
                    ]); ?>
                    <?= AddButton::widget([
                        'url' => ['run', 'id' => $model->id],
                        'text' => 'Run',
                        'options' => [
                        ]
                    ]); ?>

                    <?= AddButton::widget([
                        'url' => ['update', 'id' => $model->id],
                        'text' => 'Edit',
                        'options' => [
                        ]
                    ]); ?>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?= $this->render('_add_labels', [
                                'model' => $model
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <?php $form = ActiveForm::begin(); ?>
    <div class="card">
        <div class="card-body">
            <?=$this->render('_request_url_form_inputs', [
                'form' => $form,
                'model' => $model,
                'project' => $model->server->project
            ]); ?>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <?=$this->render('_tabs', [
                'model' => $model,
                'form' => $form
            ]); ?>

        </div>
    </div>
    <?php ActiveForm::end(); ?>
    <h3>Test results</h3>
    <div class="card">
            <?= GridView::widget([
                'summary' => false,
                'tableOptions' => ['class' => 'table table-hover'],
                'dataProvider' => new \yii\data\ActiveDataProvider([
                    'query' => $model->getResponses()->orderBy('id desc'),
                ]),
                'columns' => [
                    [
                        'header' => 'Link',
                        'format' => 'html',
                        'value' => function (ApiTestResponse $response) {
                            return Html::a('Response #'.$response->id, ['/apiTesting/response/view', 'id' => $response->id]);
                        }
                    ],
                    'created_at:relativeTime',
                    [
                        'format' => 'html',
                        'value' => function (ApiTestResponse $request) {
                            return $this->render('../common/_test_result_column', [
                                'response' => $request
                            ]);
                        }
                    ],
                    'job.name',
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


<?php $this->registerJs('
    $("form input, form select, form textarea").each(function() {
        $(this).prop("disabled", true);
    });
    
    $("form .multiple-input-list__btn").each(function() {
        $(this).hide()
    });
');
