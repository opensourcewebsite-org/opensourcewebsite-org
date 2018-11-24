<?php

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
                        <?=Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success'])?>
                        <?=Html::a(Yii::t('app', 'Cancel'), ['/issue'], [
                            'class' => 'btn btn-secondary',
                            'title' => Yii::t('app', 'Cancel'),
                        ]);?>
                        <?php if((int) $model->user_id === Yii::$app->user->identity->id || $issue->id != null && $issue->hasIssuesVoteOfOthers($issue->id)):?>
                            <?=Html::a(Yii::t('app', 'Delete'), ['issue/delete/', 'id' => $issue->id], [
                                'class' => 'btn btn-danger float-right',
                                'id' => 'delete-issue'
                            ])?>
                        <?php endif; ?>
                </div>
                </div>
            </div>
        </div>
    <?php ActiveForm::end();?>
</div>
<?php
$this->registerJs('$("#delete-issue").on("click", function(event) {
    event.preventDefault();
    var url = $(this).attr("href");

    if (confirm("' . Yii::t('app', 'Are you sure you want to delete this issue?') . '")) {
        $.post(url, {}, function(result) {
            if (result == "1") {
                location.href = "'.Yii::$app->urlManager->createUrl(['/issue']).'";
            }
            else {
                alert("' . Yii::t('app', 'Sorry, there was an error while trying to delete the issue.') . '");
            }
        });
    }
    
    return false;
});');