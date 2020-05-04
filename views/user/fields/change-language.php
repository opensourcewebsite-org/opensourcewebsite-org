<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\widgets\DeleteButton;

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
                    <?= Html::submitButton(Yii::t('app', 'Save'), [
                            'class' => 'btn btn-success',
                            'title' => Yii::t('app', 'Save')]); ?>
                    <?= Html::button(Yii::t('app', 'Cancel'), [
                            'class' => 'btn btn-secondary',
                            'data-dismiss' => 'modal',
                            'title' => Yii::t('app', 'Cancel')]); ?>
                    <?= DeleteButton::widget([
                        'url' => ['/user/delete-language', 'id' => $userLanguageRecord->id],
                        'type' => 'delete',
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
