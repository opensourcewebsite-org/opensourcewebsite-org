<?php

use app\components\helpers\TimeHelper;
use app\widgets\buttons\Cancel;
use app\widgets\buttons\Save;
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
                    <?= Save::widget(); ?>
                    <?= Cancel::widget([
                        'url' => ['/account']
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
