<?php

use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroup */
/* @var $langs app\models\SupportGroupLanguage[] */
/* @var $form yii\widgets\ActiveForm */

?>
<div class="col-12">
    <div class="card">
        <div class="card-body p-0">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col-3 p-3">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'required' => true]) ?>
            </div>

            <div class="p-3">
                <p>Languages</p>
                <?= Select2::widget([
                    'name'          => 'SupportGroupLanguage',
                    'theme'         => Select2::THEME_MATERIAL,
                    'data'          => ArrayHelper::map($languages, 'code', 'name_ascii'),
                    'value'         => $model->isNewRecord ? 'en' : ArrayHelper::getColumn($langs, 'language_code'),
                    'options'       => [
                        'placeholder' => 'Select languages',
                        'multiple'    => true,
                    ],
                    'maintainOrder' => true,
                    'pluginOptions' => [
                        'tokenSeparators' => [',', ' '],
                    ],
                ]); ?>
            </div>

            <div class="card-footer">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                <a class="btn btn-secondary" href="/support-groups">Cancel</a>
                <?php if (!$model->isNewRecord) { ?>
                    <a class="btn btn-danger float-right" href="delete?id=<?= $model->id ?>" data-method="post">Delete</a>
                <?php } ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
