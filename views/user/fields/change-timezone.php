<?php

use app\components\helpers\TimeHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

?>
<div class="profile-form">
    <?php
    $timezoneForm = ActiveForm::begin();
    ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <h3 class="card-title p-3">
                        <?= Yii::t('app', 'Edit timezone'); ?>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?php
                            $timezones = TimeHelper::timezonesList();
                            foreach ($timezones as $timezone => $fullName) {
                                $timezones[$timezone] = $fullName;
                            }

                            echo $timezoneForm->field($user, 'timezone')->widget(
                                Select2::class,
                                [
                                    'name'    => 'change-timezone',
                                    'value'   => Yii::$app->user->identity->timezone,
                                    'data'    => $timezones,
                                    'options' => [
                                        'id'     => 'timezone-value',
                                        'prompt' => '',
                                    ]
                                ])->label(Yii::t('app', 'Timezone')); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= Html::submitButton(Yii::t('app', 'Save'), [
                        'class' => 'btn btn-success',
                        'title' => Yii::t('app', 'Save')]); ?>
                    <?= Html::a(Yii::t('app', 'Cancel'), ['/account'], [
                        'class' => 'btn btn-secondary',
                        'title' => Yii::t('app', 'Cancel')]); ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
