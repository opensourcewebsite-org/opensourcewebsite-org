<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;

/* @var View $this */

?>
<div class="row">
    <div class="offset-md-2 col-md-8">
        <?php $form = ActiveForm::begin([
                'id' => 'form-wiki-token',
                'action' => Yii::$app->urlManager->createUrl(['wikinews-pages/create']),
        ]); ?>
        <div class="card-body">
            <div class="form-group">
                <?= $form->field($model, 'title') ?>
            </div>
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
                <?= Html::button(Yii::t('app', 'Close'), ['data-dismiss' => 'modal', 'class' => 'btn btn-default']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php
$js = 'var stopSubmit = false;
$("#form-wiki-token").on("beforeSubmit", function(event) {
    var action = $(this).attr("action");
    var actionCreate = "' . Yii::$app->urlManager->createUrl(['wikinews-pages/create']) . '";

    if (action == actionCreate) {
        stopSubmit = true;
        var data = $(this).serialize();
        $.get(action, data, function (result) {
            $("#main-modal-body").html(result);
        });
    } else {
        stopSubmit = false;
    }
}).on("submit", function(event) {
    if (stopSubmit) {
        event.preventDefault();
    }
});';

$this->registerJs($js);