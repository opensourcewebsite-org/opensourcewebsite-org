<?php

use app\components\helpers\ArrayHelper;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

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
                            <?= $form->field($model, 'contact_group_ids')->widget(Select2::class, [
                                    'data' => ArrayHelper::map(Yii::$app->user->identity->getContactGroups()->all(), 'id', 'name'),
                                    'showToggleAll' => false,
                                    'pluginOptions' => [
                                        'tags' => true,
                                    ],
                                    'options' => [
                                        'multiple' => true,
                                    ],
                                ])->label('Groups'); ?>
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
