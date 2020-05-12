<?php

use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;


?>
<div class="profile-form">
    <?php
    $birthdayForm = ActiveForm::begin();
    ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <h3 class="card-title p-3">
                        <?= Yii::t('app', 'Edit birthday'); ?>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= Html::label(Yii::t('app', 'Birthday'), 'birthday-value'); ?>
                            <?= DatePicker::widget([
                                'model'         => $user,
                                'name'          => 'birthday',
                                'id'            => 'birthday-value',
                                'value'         => Yii::$app->formatter->asDate($user->birthday),
                                'convertFormat' => true,
                                'pluginOptions' => [
                                    'autoclose'   => true,
                                    'format'      => Yii::$app->formatter->dateFormat,
                                ],
                            ]); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?= CancelButton::widget([
                        'url' => ['/account']
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
