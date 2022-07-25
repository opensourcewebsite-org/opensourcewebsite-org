<?php

use app\components\helpers\Html;
use app\models\Currency;
use app\models\Resume;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\SubmitButton;
use app\widgets\inputs\LocationWithMapInput\LocationWithMapInput;
use app\widgets\selects\CurrencySelect;
use app\widgets\selects\JobKeywordsSelect;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Resume */
/* @var $currencies Currency[] */

$showLocation = $model->location || $model->isNewRecord;

$labelOptional = ' (' . Yii::t('app', 'optional') . ')';

$form = ActiveForm::begin(['id' => 'form']);
?>
<div class="form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'name')->textInput() ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'skills')->textarea()
                                ->label($model->getAttributeLabel('skills') . $labelOptional); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'experiences')->textarea()
                                ->label($model->getAttributeLabel('experiences') . $labelOptional); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'expectations')->textarea()
                                ->label($model->getAttributeLabel('expectations') . $labelOptional); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?php $model->keywordsFromForm = $model->getKeywordsFromForm() ?>
                            <?= $form->field($model, 'keywordsFromForm')->widget(JobKeywordsSelect::class) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'min_hourly_rate')
                                ->textInput([
                                    'autocomplete' => 'off',
                                    'placeholder' => '∞',
                                ])
                                ->label($model->getAttributeLabel('min_hourly_rate') . $labelOptional); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'currency_id')->widget(CurrencySelect::class); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'remote_on')->checkbox(['autocomplete' => 'off']) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="offline-work-checkbox">
                                    <input id="offline-work-checkbox" type="checkbox" <?= $showLocation ? 'checked' : '' ?> autocomplete="off" />
                                    <?= Yii::t('jo', 'Offline work') ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row location-row <?= !$showLocation ? 'd-none' : '' ?>" >
                        <div class="col">
                            <?= $form->field($model, 'location')
                                ->widget(LocationWithMapInput::class)
                                ->label(Html::icon('private') . ' ' . $model->getAttributeLabel('location'))
                            ?>
                        </div>
                    </div>
                    <div class="row location-row <?= !$showLocation ? 'd-none' : '' ?>" >
                        <div class="col">
                            <?= $form->field($model, 'search_radius')
                                ->textInput(['maxlength' => true])
                                ->label(Html::icon('private') . ' ' . $model->getAttributeLabel('search_radius') . ', km')
                            ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SubmitButton::widget() ?>
                    <?php $cancelUrl = $model->isNewRecord ? Url::to('/resume/index') : Url::to(['/resume/view', 'id' => $model->id])?>
                    <?= CancelButton::widget(['url' => $cancelUrl]); ?>
                    <?php if (!$model->isNewRecord) : ?>
                        <?= DeleteButton::widget([
                            'url' => ['delete', 'id' => $model->id],
                            'options' => [
                                'data' => [
                                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                                    'method' => 'post'
                                ]
                            ]
                        ]); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
<?php
$js = <<<JS
$('#offline-work-checkbox').on('change', function () {
    $('.location-row').toggleClass('d-none');
});

$('#webresume-form').on('afterValidate', function (){
    if ($('#webresume-location').val() === '' && !$('#webresume-remote_on').is(':checked')) {
        $('#webresume-form').yiiActiveForm(
            'updateAttribute',
            'webresume-remote_on',
            ['Either Remote work or Location should be set!']
            );
        return false;
    }
    return true;
});
JS;

$this->registerJs($js);
