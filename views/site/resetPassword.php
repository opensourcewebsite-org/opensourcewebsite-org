<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

/* @var $model ResetPasswordForm */

use app\models\ResetPasswordForm;
use app\widgets\buttons\SaveButton;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = 'Reset password';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('content-header-data'); ?>
<?php $this->endBlock(); ?>
<div class="row">
    <div class="offset-md-2 col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            </div>
            <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>
            <div class="card-body">
                <p>Please choose your new password:</p>
                <div class="form-group">
                    <?= $form->field($model, 'password')->passwordInput(['autofocus' => true]) ?>
                </div>
            </div>

            <div class="card-footer">
                <div class="form-group">
                    <?= SaveButton::widget(); ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
