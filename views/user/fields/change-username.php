<?php

use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use app\widgets\buttons\DeleteButton;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin();
?>
<div class="form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <h3 class="card-title p-3">
                        <?= Yii::t('app', 'Edit username'); ?>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $form->field($user, 'username')->textInput(['value' =>
                                Yii::$app->user->identity->username])->label(Yii::t('app', 'Username')); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?= CancelButton::widget([
                        'url' => ['/account']
                    ]); ?>
                    <?= DeleteButton::widget([
                        'url' => [
                            '/user/delete-username',
                        ],
                        'visible' => Yii::$app->user->identity->username,
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
