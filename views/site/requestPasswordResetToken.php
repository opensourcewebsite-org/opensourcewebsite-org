<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

/* @var $model PasswordResetRequestForm */

use app\models\PasswordResetRequestForm;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;

$this->title = 'Request password reset';
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
          <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
          <div class="card-body">
              <p>Please fill out your email. A link to reset password will be sent there.</p>
              <div class="form-group">
                  <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>
              </div>

              <div>
                  <p>
                      If you have an account you can <?= Html::a(Yii::t('app', 'Login'), ['site/login']) ?>.
                  </p>
                  <p>
                      If you dont have an account you can <?= Html::a(Yii::t('app', 'Signup'), ['site/signup']) ?>.
                  </p>
              </div>
          </div>

          <div class="card-footer">
              <div class="form-group">
                  <?= Html::submitButton('Send', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
              </div>
          </div>

          <?php ActiveForm::end(); ?>
    </div>
</div>
