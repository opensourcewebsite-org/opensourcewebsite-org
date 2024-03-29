<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;

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
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= Yii::t('app', $userLanguage->language->name); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label><?= Yii::t('app', 'Level'); ?></label>
                                <?= Select2::widget([
                                    'name' => 'level',
                                    'data' => $languageLevels,
                                    'value' => $userLanguage->language_level_id,
                                    'options' => [
                                        'id' => 'langLevel' . $userLanguage->language_id,
                                    ],
                                ]); ?>
                        </div>
                    </div>
                </div>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?= CancelButton::widget(); ?>
                    <?= DeleteButton::widget([
                        'url' => [
                            '/user/delete-language',
                        ],
                        'options' => [
                            'data-params' => [
                                'id' => $userLanguage->id,
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
