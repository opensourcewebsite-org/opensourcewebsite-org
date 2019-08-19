<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

/* @var $model PasswordResetRequestForm */

use app\models\PasswordResetRequestForm;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = 'Request password reset';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('content-header-data'); ?>
<?php $this->endBlock(); ?>
<div class="row">
    <div class="offset-md-2 col-md-8">
        <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
        <div class="card-body">
            <p>Please fill out your email. A link to reset password will be sent there.</p>
            <div class="form-group">
                <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>