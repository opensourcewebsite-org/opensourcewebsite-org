<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $issue app\models\User */
/* @var $form yii\widgets\ActiveForm */

?>
<div class="profile-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Name...'])->label(false); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
                    <?= Html::a(Yii::t('app', 'Cancel'), ['/account'], [
                        'class' => 'btn btn-secondary',
                        'title' => Yii::t('app', 'Cancel'),
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>