<?php

use app\models\AdSection;
use app\models\Currency;
use app\models\Resume;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\CurrencySelect\CurrencySelect;
use app\widgets\AdKeywordsSelect\AdKeywordsSelect;
use app\widgets\LocationPickerWidget\LocationPickerWidget;
use app\widgets\buttons\SubmitButton;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/**
 * @var View $this
 * @var Resume $model
 * @var Currency[] $currencies
 */

$showLocation = $model->location || $model->isNewRecord;
?>
    <div class="resume-form">
        <?php $form = ActiveForm::begin(['id' => 'ad-offer-form']); ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <?= $form->field($model, 'section')->dropDownList(AdSection::getAdOfferNames(), ['prompt' => 'Select Section']) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <?= $form->field($model, 'title')->textInput() ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <?= $form->field($model, 'description')->textarea() ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <?= $form->field($model, 'price')->textInput() ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <?= $form->field($model, 'currency_id')->widget(CurrencySelect::class); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <?php $model->keywordsFromForm = $model->getKeywordsFromForm() ?>
                                <?= $form->field($model, 'keywordsFromForm')->widget(AdKeywordsSelect::class,) ?>
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
                                        <input id="offline-work-checkbox" type="checkbox" <?= $showLocation? 'checked' : '' ?> autocomplete="off" />
                                        <?= Yii::t('app', 'Offline work') ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row location-row <?= !$showLocation ? 'd-none' : '' ?>" >
                            <div class="col">
                                <?= $form->field($model, 'location')->widget(LocationPickerWidget::class) ?>
                            </div>
                        </div>
                        <div class="row location-row <?= !$showLocation ? 'd-none' : '' ?>" >
                            <div class="col">
                                <?= $form->field($model, 'search_radius')
                                    ->textInput(['maxlength' => true])
                                    ->label($model->getAttributeLabel('search_radius') . ', km')
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <?= SubmitButton::widget() ?>

                        <?php $cancelUrl = $model->isNewRecord ? Url::to('/resume/index') : Url::to(['/resume/view', 'id' => $model->id])?>
                        <?= CancelButton::widget(['url' => $cancelUrl]); ?>

                        <?php if (!$model->isNewRecord): ?>
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
        <?php ActiveForm::end(); ?>
    </div>
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
