<?php

use app\models\Language;
use app\models\LanguageLevel;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

ActiveForm::begin(); ?>
<div class="profile-form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <h3 class="card-title p-3">
                        <?= Yii::t('app', 'Add language'); ?>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <label><?= Yii::t('app', 'Choose language'); ?></label>
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
                            <label><?= Yii::t('app', 'Choose level'); ?></label>
                            <?= Select2::widget([
                                'name' => 'lvl',
                                'data' => $languagesLvl,
                                'options' => [
                                    'id' => 'newLangLvl',
                                ],
                            ]); ?>
                        </div>
                    </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= Html::submitButton(Yii::t('app', 'Save'), [
                            'class' => 'btn btn-success',
                            'title' => Yii::t('app', 'Save')]); ?>
                    <?= Html::button(Yii::t('app', 'Cancel'), [
                            'class' => 'btn btn-secondary',
                            'data-dismiss' => 'modal',
                            'title' => Yii::t('app', 'Cancel')]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
