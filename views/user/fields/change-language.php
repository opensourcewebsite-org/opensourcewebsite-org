<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\widgets\buttons\Delete;
use app\widgets\buttons\Cancel;
use app\widgets\buttons\Save;

ActiveForm::begin();
?>
<div class="profile-form">
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
                            <?= Yii::t('app', $languageName); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label><?= Yii::t('app', 'Level'); ?></label>
                                <?= Select2::widget([
                                    'name' => 'lvl',
                                    'data' => $languagesLvl,
                                    'value' => $userLanguageRecord->language_level_id,
                                    'options' => [
                                        'id' => 'langLvl' . $userLanguageRecord->language_id,
                                    ],
                                ]); ?>
                        </div>
                    </div>
                </div>
                </div>
                <div class="card-footer">
                    <?= Save::widget(); ?>
                    <?= Cancel::widget(); ?>
                    <?= Delete::widget([
                        'url' => ['/user/delete-language', 'id' => $userLanguageRecord->id],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
