<?php

use app\widgets\buttons\SaveButton;
use app\widgets\buttons\CancelButton;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\widgets\selects\CountrySelect;

$form = ActiveForm::begin();
?>
<div class="form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <div class="col">
                                <?= $form->field($model, 'country_id')
                                    ->widget(CountrySelect::class)
                                    ->label(false); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <?= SaveButton::widget([
                    'text' => Yii::t('app', 'Add'),
                    'options' => [
                        'title' => Yii::t('app', 'Add'),
                    ],
                ]); ?>
                <?= CancelButton::widget(); ?>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
