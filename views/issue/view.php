<?php

use app\components\Converter;
use app\components\helpers\IssuesHelper;
use app\models\UserIssueVote;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Issue */

$this->title = Yii::t('app', 'View Issue');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Issues'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="issue-view">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?=$model->title?></h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 d-flex">
                            <div class="mx-3 h5"><span class="badge badge-success">Open</span> </div>
                            <div class="mx-3"><span class="text-secondary">ID:</span> <?=$model->id?></div>
                            <div class="mx-3"><span class="text-secondary">Created at:</span> <?=Converter::formatDate($model->created_at)?></div>
                        </div>
                        <div class="col-6">
                                <?=IssuesHelper::getVoteHTMl($model);?>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col">
                            <p><?=nl2br($model->description);?></p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?=Html::a(Yii::t('app', 'Back'), ['/issue'], [
                        'class' => 'btn btn-primary',
                        'title' => Yii::t('app', 'Back'),
                    ]);?>
                    <div class="btn-group btn-group-toggle float-right ml-3" data-toggle="buttons">
                        <label class="btn btn-success">
                            <input type="radio" name="options" id="option1" value="<?=UserIssueVote::YES?>" autocomplete="off" <?=$model->getUserVoteSelected() == UserIssueVote::YES ? 'checked' : ''?>> Yes
                        </label>
                        <label class="btn btn-light">
                            <input type="radio" name="options" id="option2" value="<?=UserIssueVote::NEUTRAL?>" autocomplete="off" <?=$model->getUserVoteSelected() == UserIssueVote::NEUTRAL ? 'checked' : ''?>> Neutral
                        </label>
                        <label class="btn btn-danger">
                            <input type="radio" name="options" id="option3" value="<?=UserIssueVote::NO?>" autocomplete="off" <?=$model->getUserVoteSelected() == UserIssueVote::NO ? 'checked' : ''?>> No
                        </label>
                    </div>
                    <span class="text-secondary float-right my-2">Your vote weight: <?=$weightage?>%</span>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
$url = Yii::$app->urlManager->createUrl(['issue/vote']);
$script = <<<JS
$("input[name='options']:checked").click();
$("input[name='options']").on("change", function(event) {
    event.preventDefault();
    var voteType = $(this).val();

    if (confirm('Are you sure you want to vote for this issue?')) {
        $.post('{$url}', {'type':voteType, 'issue_id' : '{$model->id}'}, function(result) {
            if (result == "1") {
                location.reload();
            }
            else {
                alert('Sorry, there was an error while trying to vote the issue');
            }
        });
    }

    return false;
});
JS;
$this->registerJs($script);