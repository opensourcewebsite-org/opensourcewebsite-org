<?php
/* @var $this \yii\web\View */

use app\widgets\buttons\Cancel;
use app\widgets\buttons\Save;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\assets\AceEditorAsset;

$this->title = Yii::t('menu', ($moqup->isNewRecord) ? 'Add design' : 'Edit design');
$this->beginBlock('content-header-data');
$this->endBlock();

AceEditorAsset::register($this);
?>
<style id="prev-style"></style>
<?php $form = ActiveForm::begin([
    'id' => 'deign-edit-form'
]); ?>
<div class="card">
    <div class="card-header d-flex p-0">
        <h3 class="card-title p-3">
            <?= ($moqup->isNewRecord) ? Yii::t('moqup', 'Add Moqup') : Yii::t('moqup', 'Edit Moqup') ?>
        </h3>
    </div>
    <div class="card-body">

        <div class="alert alert-info">
            <h5><i class="icon fa fa-info"></i> Important!</h5>
            Use UI elements from <?= Html::a('AdminLTE 3', 'https://adminlte.io/themes/dev/AdminLTE/index3.html') ?>, <?= Html::a('Bootstrap 4', 'https://getbootstrap.com/docs/4.1/getting-started/introduction/') ?> and <?= Html::a('Font Awesome 5', 'https://fontawesome.com/icons') ?> examples.
        </div>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($moqup, 'title')->textInput() ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?= Html::ul([
                    Html::a('HTML', '#html', ['class' => 'nav-link active', 'data-toggle' => 'tab']),
                    Html::a('CSS (optional)', '#css', ['class' => 'nav-link', 'data-toggle' => 'tab']),
                    Html::a('Preview', '#preview', ['class' => 'nav-link', 'data-toggle' => 'tab', 'id' => 'toggle-prev']),
                ], [
                    'class' => 'nav nav-pills ml-auto p-2',
                    'encode' => false,
                    'itemOptions' => ['class' => 'nav-item']
                ]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="tab-content p-0">
                    <div class="tab-pane active" id="html">
                        <?= $form->field($moqup, 'html')->textArea([
                            'rows' => 10,
                            'max-length' => 100000,
                            'style' => 'display:none',
                        ])->label(false) ?>
                        <div id="html-editor" class="ace-editor"><?= Html::encode($moqup->html) ?></div>
                    </div>
                    <div class="tab-pane" id="css">
                        <?= $form->field($css, 'css')->textArea([
                            'rows' => 10,
                            'max-length' => 100000,
                            'style' => 'display:none',
                        ])->label(false) ?>
                        <div id="css-editor" class="ace-editor"><?= Html::encode($css->css) ?></div>
                    </div>
                    <div class="tab-pane" id="preview">
                        <div class="row">
                            <div id="prev-content" class="col-md-12"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <?= Save::widget(); ?>
        <?= Cancel::widget([
            'url' => ['moqup/design-list'],
        ]); ?>

        <?php if (!$moqup->isNewRecord): ?>
            <?= Html::a(Yii::t('app', 'Delete'), '#', [
                'class' => 'btn btn-danger float-right',
                'onclick' => 'if (confirm("' . Yii::t('moqup', 'Are you sure you want to delete this moqup?') . '")) {
                    $.post("' . (Yii::$app->urlManager->createUrl(['moqup/design-delete/', 'id' => $moqup->id])) . '", {}, function(result) {
                        if (result == "1") {
                            location.href="' . (Yii::$app->urlManager->createUrl(['moqup/design-list', 'viewYours' => true])) . '";
                        }
                        else {
                            alert("' . Yii::t('moqup', 'Sorry, there was an error while trying to delete the moqup') . '");
                        }
                    });
                }',
            ]) ?>
        <?php endif; ?>
    </div>
</div>
<?php
ActiveForm::end();

//Prepare the preview
$this->registerjs('$("#toggle-prev").on("show.bs.tab", function() {
    var prevCont = $("#prev-content");
    var prevStyle = $("#prev-style");

    var currentCont = htmlEditor.getValue();
    var currentStyle = cssEditor.getValue();

    prevCont.html(currentCont);
    prevStyle.html(currentStyle);
})');

//Activate the AceEditor
$this->registerJs('htmlEditor = ace.edit("html-editor");
    htmlEditor.setTheme("ace/theme/chrome");
    htmlEditor.session.setMode("ace/mode/html");

    cssEditor = ace.edit("css-editor");
    cssEditor.setTheme("ace/theme/chrome");
    cssEditor.session.setMode("ace/mode/css");');

//Set the AceEditor values to the model inputs
$this->registerJs('$("#deign-edit-form").on("beforeValidate beforeSubmit", function(e) {
    var htmlVal = htmlEditor.getValue();
    $("#moqup-html").val(htmlVal);
    var cssVal = cssEditor.getValue();
    $("#css-css").val(cssVal);
});');
