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

                <p>Languages</p>
                <div class="form-check">
                    <?= Html::checkbox('SupportGroupLanguage[' . (ArrayHelper::isIn('en',
                            ArrayHelper::getColumn($langs, 'language_code')) ? array_search('en',
                            ArrayHelper::getColumn($langs, 'language_code')) : '') . '][language_code]',
                        ($model->isNewRecord ? true : ArrayHelper::isIn('en', ArrayHelper::getColumn($langs, 'language_code'))),
                        ['value' => 'en', 'label' => 'English']) ?>
                </div>
                <div class="form-check">
                    <?= Html::checkbox('SupportGroupLanguage[' . (ArrayHelper::isIn('ru',
                            ArrayHelper::getColumn($langs, 'language_code')) ? array_search('ru',
                            ArrayHelper::getColumn($langs, 'language_code')) : '') . '][language_code]',
                        ($model->isNewRecord ? false : ArrayHelper::isIn('ru', ArrayHelper::getColumn($langs, 'language_code'))),
                        ['value' => 'ru', 'label' => 'Russian']) ?>
                </div>
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
