<?php
/* @var $this \yii\web\View */

use yii\helpers\Html;
use app\modules\comment\models\MoqupComment;
use app\modules\comment\Comment;

$this->title = Yii::t('menu', 'View design');
\app\assets\AceEditorAsset::register($this);
$followed = in_array($moqup->id, Yii::$app->user->identity->followedMoqupsId);

if (!empty($moqup)):
?>
<style id="prev-style"></style>
    <div class="card">
        <div class="card-header d-flex p-0">
            <h3 class="card-title p-3">
                <?= $moqup->title; ?>
                <small class="'class' => 'ml-2 text-secondary'">
                    by <?= Html::a($moqup->user->username, '') ?>
                    <?php if ($moqup->forked_of != null): ?>
                        (Forked from <?= Html::a($moqup->origin->title, ['moqup/design-view', 'id' => $moqup->forked_of]) ?>)
                    <?php endif; ?>
                </small>
            </h3>
            <div class="ml-auto p-2">
                <?php if ((int)$moqup->user_id === Yii::$app->user->identity->id) : ?>
                <?= Html::a('<i class="fas fa-edit"></i>', ['moqup/design-edit', 'id' => $moqup->id], [
                    'class' => 'btn btn-light',
                    'title' => 'Edit',
                ]) ?>
                <?php endif; ?>
                <?= Html::a(Html::tag('i', '', ['class' => 'fa fa-code-branch'])
                    . Html::tag('span', $moqup->forksNumber, ['class' => 'badge badge-light ml-1']),
                    ['moqup/design-edit', 'fork' => $moqup->id], [
                        'class' => 'btn btn-light',
                        'title' => 'Fork Page'
                    ]) ?>

                <?= Html::a(Html::tag('i', '', ['class' => 'fa fa-star' . ($followed ? ' text-primary' : '')])
                    . Html::tag('span', $moqup->followersNumber, ['class' => 'badge badge-light ml-1']),
                    [($followed ? 'user/unfollow-moqup' : 'user/follow-moqup'), 'id' => $moqup->id], [
                        'class' => 'btn btn-light ' . ($followed ? 'unfollow-page' : 'follow-page'),
                        'title' => $followed ? 'Unfollow Page' : 'Follow Page',
                    ]) ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12">
                    <?= Html::ul([
                        Html::a('View', '#view', ['class' => 'nav-link active', 'data-toggle' => 'tab']),
                        Html::a('HTML', '#html', ['class' => 'nav-link', 'data-toggle' => 'tab']),
                        Html::a('CSS', '#css', ['class' => 'nav-link', 'data-toggle' => 'tab', 'style' => ($css != null ? '' : 'display:none')]),
                    ], [
                        'class' => 'nav nav-tabs',
                        'encode' => false,
                        'itemOptions' => ['class' => 'nav-item']
                    ]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="tab-content">
                        <div class="tab-pane active" id="view">
                            <div id="prev-content"></div>
                        </div>
                        <div class="tab-pane" id="html">
                            <div id="html-editor" class="ace-editor"><?= Html::encode($moqup->html) ?></div>
                        </div>
                        <div class="tab-pane" id="css">
                            <div id="css-editor" class="ace-editor"><?= ($css != NULL) ? Html::encode($css->css) : NULL ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= Comment::widget([
    'model' => MoqupComment::class,
    'material' => 2,
    'related' => 'moqup_id',
]);

?>

<?php
//Activate the AceEditor
$this->registerJs('htmlEditor = ace.edit("html-editor");
htmlEditor.setTheme("ace/theme/chrome");
htmlEditor.session.setMode("ace/mode/html")
htmlEditor.setReadOnly(true);

cssEditor = ace.edit("css-editor");
cssEditor.setTheme("ace/theme/chrome");
cssEditor.session.setMode("ace/mode/css");
cssEditor.setReadOnly(true);

var prevCont = $("#prev-content");
var prevStyle = $("#prev-style");

var currentCont = htmlEditor.getValue();
var currentStyle = cssEditor.getValue();

prevCont.html(currentCont);
prevStyle.html(currentStyle);


$(".follow-page, .unfollow-page, .follow-user, .unfollow-user").on("click", function(event) {
    event.preventDefault();
    var url = $(this).attr("href");

    $.post(url, {}, function(result) {
        if (result == "1") {
            location.reload();
        } else {
            alert("' . Yii::t('moqup', 'Sorry, there was an error while trying to process your requirement') . '");
        }
    });

    return false;
});');
