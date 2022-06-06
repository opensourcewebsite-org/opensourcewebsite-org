<?php

use app\components\helpers\ArrayHelper;
use app\components\helpers\Html;
use app\models\Company;
use app\models\Currency;
use app\models\forms\LanguageWithLevelsForm;
use app\models\Gender;
use app\models\Language;
use app\models\LanguageLevel;
use app\models\Vacancy;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\SubmitButton;
use app\widgets\CompanySelectCreatable\CompanySelectCreatable;
use app\widgets\LocationPickerWidget\LocationPickerWidget;
use app\widgets\selects\CurrencySelect;
use app\widgets\selects\JobKeywordsSelect;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/**
 * @var View $this
 * @var Vacancy $model
 * @var LanguageWithLevelsForm $languageWithLevelsForm
 * @var Currency[] $currencies
 * @var Company[] $companies
 */

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
                            <?= $form->field($model, 'requirements')->textarea() ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'conditions')->textarea() ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'responsibilities')->textarea() ?>
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
                            <?= $form->field($model, 'max_hourly_rate')
                            ->textInput([
                                'autocomplete' => 'off',
                                'placeholder' => '∞',
                            ])
                            ->label($model->getAttributeLabel('max_hourly_rate') . $labelOptional); ?>
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
                                    <?= Yii::t('app', 'Offline work') ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row location-row <?= !$showLocation ? 'd-none' : '' ?>">
                        <div class="col">
                            <?= $form->field($model, 'location')
                                ->widget(LocationPickerWidget::class)
                                ->label(Html::icon('private') . ' ' . $model->getAttributeLabel('location'))
                            ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'gender_id')->dropDownList(
                                ArrayHelper::map(Gender::find()->all(), 'id', 'name'),
                                ['prompt' => Yii::t('app', 'All')]
                            ) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'company_id')->widget(CompanySelectCreatable::class, [
                                'companies' => ArrayHelper::map($companies, 'id', 'name'),
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SubmitButton::widget() ?>
                    <?php $cancelUrl = $model->isNewRecord ? Url::to('/vacancy/index') : Url::to(['/vacancy/view', 'id' => $model->id])?>
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

$('#webvacancy-form').on('afterValidate', function (){
    if ($('#webvacancy-location').val() === '' && !$('#webvacancy-remote_on').is(':checked')) {
        $('#webvacancy-form').yiiActiveForm(
            'updateAttribute',
            'webvacancy-remote_on',
            ['Either Remote work or Location should be set!']
            );
        return false;
    }
    return true;
});

JS;

$this->registerJs($js);
