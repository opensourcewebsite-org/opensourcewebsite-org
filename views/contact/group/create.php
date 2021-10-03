<?php

use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\SaveButton;
use yii\widgets\ActiveForm;
use yii\helpers\Html;

$form = ActiveForm::begin([
        'enableAjaxValidation' => true,
]);
?>
<div class="form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'name')->textInput(); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <?= SaveButton::widget([
                    'text' => $model->isNewRecord ? Yii::t('app', 'Add') : Yii::t('app', 'Save'),
                    'options' => [
                        'title' => $model->isNewRecord ? Yii::t('app', 'Add') : Yii::t('app', 'Save'),
                    ],
                ]); ?>
                <?= CancelButton::widget(); ?>
                <?= DeleteButton::widget([
                    'url' => [
                        '/contact/delete-group',
                        'id' => $model->id,
                    ],
                    'visible' => !$model->isNewRecord,
                ]);?>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
