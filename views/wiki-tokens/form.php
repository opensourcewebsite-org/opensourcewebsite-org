<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use app\models\WikiLanguage;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;
use app\models\UserWikiToken;

/* @var View $this */
/* @var UserWikiToken $model */

?>
<div class="row">
    <div class="offset-md-2 col-md-8">
        <?php $form = ActiveForm::begin([
                'action' => [
                    'wiki-tokens/' . ($model->isNewRecord ? 'create?language_id=' . $model->language_id : 'update?id=' . $model->id),
                ],
        ]); ?>
        <div class="card-body">
            <?php if ($model->isNewRecord): ?>
                <div class="form-group">
                    <?= $form->field($model, 'language_id')->widget(Select2::class, [
                        'data' => ArrayHelper::map(WikiLanguage::find()->where(
                                [
                                    'not in', 'id',
                                        UserWikiToken::find()
                                        ->select('id')
                                        ->where(['user_id' => Yii::$app->user->id]),
                            ])->all(), 'id', function ($language) {
                                return "{$language['name']} ({$language['code']}.wikipedia.org)";
                            }),
                        'options' => [
                            'prompt' => '',
                            'title' => '',
                        ],
                        'pluginEvents' => [
                            "change" => "function() { $('#main-modal').find('#main-modal-body').load('" . Yii::$app->urlManager->createUrl(['wiki-tokens/create']) . "?language_id=' + $(this).val()) }",
                        ],
                    ])->label(false); ?>
                </div>
            <?php endif; ?>
            <?php if ($model->language_id): ?>
                <div class="form-group">
                    <?= $form->field($model, 'wiki_username') ?>
                    <p><?= Html::a(Yii::t('app', 'Look your username here'), "https://{$model->language->code}.wikipedia.org/wiki/Special:Preferences", ['target' => '_blank']) ?></p>
                </div>
                <div class="form-group">
                    <?= $form->field($model, 'token') ?>
                    <p><?= Html::a(Yii::t('app', 'Look your token here'), "https://{$model->language->code}.wikipedia.org/wiki/Special:Preferences#mw-prefsection-watchlist", ['target' => '_blank']) ?></p>
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
            <?php endif; ?>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php
$js = <<<JS
$('.select2-selection__rendered').tooltip('disable');
JS;

$this->registerJs($js);