<?php

use app\components\helpers\ArrayHelper;
use app\components\helpers\Icon;
use app\modules\apiTesting\models\ApiTestServer;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\SaveButton;
use app\widgets\ModalAjax;
use yii\bootstrap4\Tabs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView as DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\apiTesting\models\ApiTestRequest */
/* @var $form yii\widgets\ActiveForm */
/* @var $server ApiTestServer */
?>
<div class="api-test-request-form">
    <?php
    $form = ActiveForm::begin([
        'id' => 'request-form',
        'fieldConfig' => [
            'options' => [
                //            'tag' => false,
            ],
        ]
    ]);
    ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if ( ! $model->isNewRecord):?>
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <?=$this->render('_add_labels', [
                                    'model' => $model
                                ]); ?>
                            </div>
                            <div class="col-md-12">

                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="row">
                        <?php if ($model->isNewRecord):?>
                            <div class="col-md-12">
                                <?= $form->field($model, 'name')->textInput(['maxlength' => true]); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <br>
                    <?=$this->render('_request_url_form_inputs', [
                        'form' => $form,
                        'model' => $model,
                        'project' => $project
                    ]); ?>
                    <br>
                    <?php if ( ! $model->isNewRecord):?>
                        <div class="row">
                            <div class="col-md-12">
                                <?=$this->render('_tabs', [
                                    'model' => $model,
                                    'form' => $form
                                ]); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?= CancelButton::widget([
                        'url' => ['/apiTesting/project/testing', 'id' => $project->id]
                    ]); ?>
                    <?php if ( ! $model->isNewRecord):?>
                        <?= DeleteButton::widget([
                            'url' => ['delete', 'id' => $model->id],
                            'options' => [
                                'id' => 'delete-request',
                            ]
                        ]); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>

