<?php

use app\widgets\buttons\SaveButton;
use app\widgets\buttons\CancelButton;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

ActiveForm::begin(); ?>
<div class="profile-form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <label><?= Yii::t('app', 'Citizenship'); ?></label>
                            <?= Select2::widget([
                                'name' => 'country',
                                'data' => $citizenships,
                                'options' => [
                                    'id' => 'newCitizenship',
                                ],
                            ]); ?>
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
</div>
<?php ActiveForm::end(); ?>
