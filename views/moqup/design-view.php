<?php
/* @var $this \yii\web\View */

use yii\helpers\Html;

$this->title = Yii::t('menu', 'View design');
\app\assets\AceEditorAsset::register($this);
$followed = in_array($moqup->id, Yii::$app->user->identity->followedMoqupsId);

if (!empty($moqup)):
?>
    <div class="card">
        <div class="card-header d-flex p-0">
            <h3 class="card-title p-3">
                <?= $moqup->title; ?>
                <small class="'class' => 'ml-2 text-secondary'">
                    by <?= Html::a($moqup->user->username, '') ?>
                </small>
            </h3>
            <div class="ml-auto p-2">
                <?= Html::a(Html::tag('i', '', ['class' => 'fa fa-code-branch'])
                    . Html::tag('span', 0, ['class' => 'badge badge-light ml-1']),
                    '', [
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
                        Html::a('Css', '#css', ['class' => 'nav-link', 'data-toggle' => 'tab', 'style' => ($css != null ? '' : 'display:none')]),
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
                            <iframe id="prev-frame" src="<?= yii::$app->urlManager->createUrl(['moqup/design-preview']) ?>" frameborder="0" sandbox="allow-forms allow-popups allow-same-origin allow-scripts allow-pointer-lock" class="col-md-12"></iframe>
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

var prevFrame = $("#prev-frame").contents();
var prevCont = prevFrame.find("#prev-content");
var prevStyle = prevFrame.find("#prev-style");

var currentCont = htmlEditor.getValue();
var currentStyle = cssEditor.getValue();

prevCont.html(currentCont);
prevStyle.html(currentStyle);

var prevHeight = $("#prev-frame").contents().height();
console.log(prevHeight);
$("#prev-frame").css("min-height", prevHeight + "px");

$(".follow-page, .unfollow-page, .follow-user, .unfollow-user").on("click", function(event) {
    event.preventDefault();
    var url = $(this).attr("href");
    var message = "";

    if ($(this).hasClass("follow-page")) {
        message = "' . Yii::t('user', 'Are you sure you want to follow this moqup?') . '";
    } else if ($(this).hasClass("unfollow-page")) {
        message = "' . Yii::t('user', 'Are you sure you want to unfollow this moqup?') . '";
    } else if ($(this).hasClass("follow-user")) {
        message = "' . Yii::t('user', 'Are you sure you want to follow this user?') . '";
    } else if ($(this).hasClass("unfollow-user")) {
        message = "' . Yii::t('user', 'Are you sure you want to unfollow this user?') . '";
    }
    
    if (confirm(message)) {
        $.post(url, {}, function(result) {
            if (result == "1") {
                location.reload();
            } else {
                alert("' . Yii::t('moqup', 'Sorry, there was an error while trying to process your requirement') . '");
            }
        });
    }

    return false;
});');