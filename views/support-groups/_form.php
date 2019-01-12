<?php

use app\models\SupportGroupLanguage;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroup */
/* @var $langs app\models\SupportGroupLanguage */
/* @var $form yii\widgets\ActiveForm */

?>
<div class="col-12">
    <div class="card">
        <div class="card-body p-0">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col-3 p-3">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
            </div>

            <div class="p-3">
                <p>Languages</p>
                <?= $form->field(new SupportGroupLanguage(), '[]language_code')->checkboxList(ArrayHelper::map($languages,'code','name_ascii'), [
                    'item' => function($index, $label, $name, $checked, $value){
                        $check = $checked ? ' checked="checked"' : '';
                        $name = 'SupportGroupLanguage['. ($index + 1) .'][language_code]';
                        return "<div class='col-3'><div class='form-check'><input type=\"checkbox\" class='form-check-input' name=\"$name\" value=\"$value\" id=\"$name$value\" $check><label class='form-check-label' for=\"$name$value\">$label</label></div></div>";
                    },
                    'class' => 'row',
                    'value' => $model->isNewRecord ? 'en' : ArrayHelper::getColumn($langs, 'language_code')
                ])->label(false); ?>
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
