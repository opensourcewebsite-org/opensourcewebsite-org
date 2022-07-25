<?php

use app\widgets\buttons\SaveButton;
use app\widgets\buttons\CancelButton;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

ActiveForm::begin();
?>
<div class="form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <label><?= Yii::t('app', 'Language'); ?></label>
                            <?= Select2::widget([
                                'name' => 'language',
                                'data' => $languages,
                                'options' => [
                                    'id' => 'newLang',
                                ],
                            ]); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label><?= Yii::t('app', 'Level'); ?></label>
                            <?= Select2::widget([
                                'name' => 'level',
                                'data' => $languageLevels,
                                'options' => [
                                    'id' => 'newLangLevel',
                                ],
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <?= SaveButton::widget(); ?>
                <?= CancelButton::widget() ?>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
