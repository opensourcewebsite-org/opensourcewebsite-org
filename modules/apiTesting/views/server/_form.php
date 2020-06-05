<?php

use app\models\Issue;
use app\modules\apiTesting\models\ApiTestProject;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\SaveButton;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\apiTesting\models\ApiTestServer */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="api-test-server-form">
    <?php
    $form = ActiveForm::begin(['fieldConfig' => [
        'options' => [
            'tag' => false,
        ],
    ]]);
    ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <?= $form->field($model, 'protocol')->dropDownList($model::getProtocolList()); ?>
                        </div>
                        <div class="col-md-2">
                            <?= $form->field($model, 'domain')->textInput(['maxlength' => true]); ?>
                        </div>
                        <div class="col-md-2">
                            <?= $form->field($model, 'path')->textInput(['maxlength' => true]); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <hr>
                            <strong>Ok, now let's add the new server at your project.</strong>
                            <p>Log in to your domain provider's website and setup your DNS record as follows:</p>
                            <p>For your domain create a TXT record: <strong><?=$model->txt; ?></strong></p>
                            <br>
                            <strong>Thats it!</strong>
                            <p>If you entered your DNS record correctly, your new server should be live within 72 hours</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?= CancelButton::widget([
                        'url' => ['index', 'id' => $model->project_id]
                    ]); ?>
                    <?php if ( ! $model->isNewRecord):?>
                        <?= DeleteButton::widget([
                            'url' => ['delete', 'id' => $model->id],
                            'options' => [
                                'id' => 'delete-project',
                            ]
                        ]); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
