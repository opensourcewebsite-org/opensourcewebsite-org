<?php

use app\assets\AceEditorAsset;
use app\assets\AdminLteContributingAsset;
use yii\helpers\Html;

$this->registerAssetBundle(AdminLteContributingAsset::class);

$this->title = Yii::t('app', 'Editors');
$this->params['breadcrumbs'][] = $this->title;

AceEditorAsset::register($this);

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

$this->registerJs('$("#trumbowyg").trumbowyg();');
?>
<!DOCTYPE html>
<html>
<section class="content">
    <div class="card">
        <div class="card-header d-flex p-0">
            <h3 class="card-title p-3">
                New note
            </h3>
        </div>
        <div class="card-body">

            <div class="alert alert-info">
                <h5><i class="icon fa fa-info"></i> Important!</h5>
                Use UI elements from <?= Html::a('AdminLTE 3', 'https://adminlte.io/themes/dev/AdminLTE/index3.html') ?>
                , <?= Html::a('Bootstrap 4', 'https://getbootstrap.com/docs/4.1/getting-started/introduction/') ?>
                and <?= Html::a('Font Awesome 5', 'https://fontawesome.com/icons') ?> examples.
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?= Html::textInput('textinput', '', [
                        'class' => 'form-control',
                    ]); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?= Html::ul([
                        Html::a('HTML', '#html', ['class' => 'nav-link active', 'data-toggle' => 'tab']),
                        Html::a('CSS (optional)', '#css', ['class' => 'nav-link', 'data-toggle' => 'tab']),
                        Html::a('Preview', '#preview', [
                            'class' => 'nav-link', 'data-toggle' => 'tab', 'id' => 'toggle-prev',
                        ]),
                    ], [
                        'class' => 'nav nav-pills ml-auto p-2',
                        'encode' => false,
                        'itemOptions' => ['class' => 'nav-item'],
                    ]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="tab-content p-0">
                        <div class="tab-pane active" id="html">
                            <?= Html::textArea('textarea', '', [
                                'rows' => 10,
                                'max-length' => 100000,
                                'style' => 'display:none',
                            ]) ?>
                            <div id="html-editor" class="ace-editor"></div>
                        </div>
                        <div class="tab-pane" id="css">
                            <?= Html::textArea('textarea', '', [
                                'rows' => 10,
                                'max-length' => 100000,
                                'style' => 'display:none',
                            ]) ?>
                            <div id="css-editor" class="ace-editor"></div>
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
    </div>
    <div class="card">
        <div id="trumbowyg"></div>
    </div>
</section>
<a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button" aria-label="Scroll to top">
    <i class="fas fa-chevron-up"></i>
</a>
<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->

</html>

