<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\WikinewsPage */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="issue-form">
    <?php $form = ActiveForm::begin(['action'=>['create'],'id' => 'wikinews-pages-form', 'enableClientValidation' => true]); ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                           <?=$form->field($model, 'language_id')->dropDownList($language_arr,['prompt'=>'Select Language'])?>
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="col-md-12">
                            <?=$form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'Title'])?>
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="col-md-12">
                            <?=$form->field($model, 'wikinews_page_url')->textInput(['maxlength' => true, 'placeholder' => 'URL'])?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                        <?=Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success'])?>
                        <?=Html::a(Yii::t('app', 'Cancel'), ['/wikinews-pages'], [
                            'class' => 'btn btn-secondary',
                            'title' => Yii::t('app', 'Cancel'),
                        ]);?>
                </div>
                </div>
            </div>
        </div>
    <?php ActiveForm::end();?>
</div>
