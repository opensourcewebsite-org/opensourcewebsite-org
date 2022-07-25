<?php

use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\SaveButton;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $issue app\models\Issue */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="issue-form">
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
                <div class="card-header">
                    <h3 class="card-title">
                    <?=$form->field($issue, 'title')->textInput(['maxlength' => true, 'placeholder' => 'Title...'])->label(false)?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?=$form->field($issue, 'description')->textarea(['rows' => 10, 'placeholder' => 'Description...'])->label(false)?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?= CancelButton::widget([
                        'url' => '/issue'
                    ]); ?>
                        <?php if((int) $issue->user_id === Yii::$app->user->identity->id && $issue->id != null && !$issue->hasIssuesVoteOfOthers($issue)):?>
                        <?= DeleteButton::widget([
                            'url' => ['issue/delete/', 'id' => $issue->id],
                            'options' => [
                                'id' => 'delete-issue',
                            ]
                        ]); ?>
                        <?php endif; ?>
                </div>
                </div>
            </div>
        </div>
    <?php ActiveForm::end();?>
</div>
