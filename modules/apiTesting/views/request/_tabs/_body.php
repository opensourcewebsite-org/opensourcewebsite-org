<?php
/**
 * @var $model \app\modules\apiTesting\models\ApiTestRequest
 * @var $form  \yii\widgets\ActiveForm
 */
?>
<div class="row">
    <div class="col-md-3">
        <?= $form->field($model, 'content_type')->dropDownList($model::getContentTypesList()); ?>
    </div>
</div>
<br>
<div class="row">
    <div class="col-md-12">
        <?= $form->field($model, 'body')->textArea([
            'rows' => 10,
            'id' => 'request-body',
            'max-length' => 10000000,
            'style' => 'display:none',
        ])->label(false); ?>
    </div>
</div>

<div id="json-editor" class="ace-editor"><?= $model->body; ?></div>

<?php
//Activate the AceEditor
$this->registerJs('
    $(function() {
        jsonEditor = ace.edit("json-editor");
        jsonEditor.setTheme("ace/theme/chrome");
        jsonEditor.session.setMode("ace/mode/json");
    });
', \yii\web\View::POS_READY);

//Set the AceEditor values to the model inputs
$this->registerJs('$("#request-form").on("beforeValidate beforeSubmit", function(e) {
    var htmlVal = jsonEditor.getValue();
    $("#request-body").val(htmlVal);
});', \yii\web\View::POS_READY);
