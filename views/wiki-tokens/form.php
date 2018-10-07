<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;
use app\models\UserWikiToken;

/* @var View $this */
/* @var UserWikiToken $model */

?>
<div class="row">
    <div class="offset-md-2 col-md-8">
        <?php $form = ActiveForm::begin([
                'id' => 'form-wiki-token',
                'action' => Yii::$app->urlManager->createUrl($model->isNewRecord ? ['wiki-tokens/create'] : ['wiki-tokens/update', 'id' => $model->id]),
        ]); ?>
        <div class="card-body">
            <?php if ($model->isNewRecord): ?>
                <div class="form-group">
                    <?= $form->field($model, 'language_id')->widget(Select2::class, [
                        'data' => ArrayHelper::map($languageArray, 'id', function ($language) {
                                return "{$language['name']} ({$language['code']}.wikipedia.org)";
                            }),
                        'options' => [
                            'prompt' => '',
                            'title' => '',
                            'options' => ArrayHelper::map($languageArray, 'id', function ($data) {
                                return ['data-code' => $data->code];
                            }),
                        ],
                        'pluginEvents' => [
                            'change' => 'function() {
                                var language = $(this).children("option:selected").attr("data-code");
                                var targetUsername = "https://" + language + ".wikipedia.org/wiki/Special:Preferences";
                                var targetToken = "https://" + language + ".wikipedia.org/wiki/Special:Preferences#mw-prefsection-watchlist";
                                
                                $("#aUsername").attr("href", targetUsername).show();
                                $("#aToken").attr("href", targetToken).show();
                            }',
                        ],
                    ])->label(false); ?>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <?= $form->field($model, 'wiki_username') ?>
                <p><?= Html::a(Yii::t('app', 'Look your username here'), '', [
                    'target' => '_blank', 
                    'id' => 'aUsername',
                    'style' => 'display:none',
                    ]) ?></p>
            </div>
            <div class="form-group">
                <?= $form->field($model, 'token', ['errorOptions' => ['encode' => false]]) ?>
                <p><?= Html::a(Yii::t('app', 'Look your token here'), '', [
                    'target' => '_blank',
                    'id' => 'aToken',
                    'style' => 'display:none',
                    ]) ?></p>
            </div> 
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
                <?= Html::button(Yii::t('app', 'Close'), ['data-dismiss' => 'modal', 'class' => 'btn btn-default']) ?>
                <?php if (!$model->isNewRecord) : ?>
                    <?= Html::a(Yii::t('app', 'Delete'), ['wiki-tokens/delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data-pjax' => '0',
                        'data-confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                        'data-method' => 'post',
                        'style' => ['float' => 'right'],
                    ]); ?>
                <?php endif; ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php
$js = 'var stopSubmit = false;
$("#form-wiki-token").on("beforeSubmit", function(event) {
    var action = $(this).attr("action");
    var actionCreate = "' . Yii::$app->urlManager->createUrl(['wiki-tokens/create']) . '";
    var actionUpdate = "' . Yii::$app->urlManager->createUrl(['wiki-tokens/update', 'id' => $model->id]) . '";

    if (action == actionCreate || action == actionUpdate) {
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