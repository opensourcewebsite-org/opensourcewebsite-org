<?php

use app\components\Converter;
use app\components\helpers\IssuesHelper;
use app\models\UserIssueVote;
use app\widgets\buttons\EditButton;
use yii\helpers\Html;
use app\modules\comment\models\IssueComment;
use app\modules\comment\Comment;

/* @var $this yii\web\View */
/* @var $model app\models\Issue */

$this->title = Yii::t('app', 'View Issue');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Issues'), 'url' => ['index']];
$this->params['breadcrumbs'][] = '#'.$model->id;
?>
<div class="issue-view">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-11">
                            <h3 class="card-title"><?=$model->title?></h3>
                        </div>
                        <div class="col-1 text-right">
                            <?php if ((int) $model->user_id === Yii::$app->user->identity->id && !$model->hasIssuesVoteOfOthers($model)) : ?>
                            <?= EditButton::widget([
                                'url' => ['issue/edit', 'id' => $model->id]
                                ]); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 d-flex">
                            <div class="mx-3 h5"><span class="badge badge-success">Open</span> </div>
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
                    <div class="btn-group btn-group-toggle float-right ml-3" data-toggle="buttons">
                        <label class="btn btn-success">
                            <input type="radio" name="options" id="option1" value="<?=UserIssueVote::YES?>" autocomplete="off" <?=$model->getUserVoteSelected() == UserIssueVote::YES ? 'checked' : ''?>> Yes
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

<?= Comment::widget([
    'model' => IssueComment::class,
    'material' => $model->id,
    'related' => 'issue_id',
]); ?>


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
