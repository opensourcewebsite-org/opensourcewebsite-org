<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap4\ActiveForm */

/* @var $model PasswordResetRequestForm */

use yii\bootstrap4\ActiveForm;
use yii\captcha\Captcha;
use yii\helpers\Html;

$this->title = 'Request to reset password';
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
          <?php $form = ActiveForm::begin([
              'id' => 'request-reset-password-form',
          ]); ?>
          <div class="card-body">
              <p><?= Yii::t('app', 'Enter your email to receive further instructions and a link to reset your password') ?>.</p>
              <div class="form-group">
                  <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>
              </div>
              <div class="form-group">
                <?= $form->field($model, 'captcha')->widget(Captcha::className(), [
                    'template' => '{image}' . Html::button('<span class="fas fa-refresh"></span>', ['id' => 'refresh-captcha', 'class' => 'btn btn-primary']) . '{input}'
                ]) ?>
                <?= $this->registerJs("
                        $('#refresh-captcha').on('click', function(e){
                            e.preventDefault();
                            $('#requestresetpasswordform-captcha-image').yiiCaptcha('refresh');
                        })
                    ");
                ?>
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
