<?php

use app\components\helpers\ArrayHelper;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use app\widgets\selects\ContactGroupSelect;

/* @var $this yii\web\View */
/* @var $model app\models\Contact */

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
                            <?php $model->groupIds = $model->getGroupIds() ?>
                            <?= $form->field($model, 'groupIds')
                                ->widget(ContactGroupSelect::class); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <?= SaveButton::widget(); ?>
                <?= CancelButton::widget(); ?>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
