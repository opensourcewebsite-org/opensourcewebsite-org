<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

/* @var $model LoginForm */

use app\models\LoginForm;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = 'Login';
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
            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
            <div class="card-body">
                <div class="form-group">
                    <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>
                </div>
                <div class="form-group">
                    <?= $form->field($model, 'password')->passwordInput() ?>
                </div>

                <div>
                    <p>
                        If you forgot your password you can <?= Html::a(Yii::t('app', 'Restore password'), ['site/request-password-reset']) ?>.
                    </p>
                    <p>
                        If you dont have an account you can <?= Html::a(Yii::t('app', 'Signup'), ['site/signup']) ?>.
                    </p>
                    <p>
                        By continuing, you agree to our <?= Html::a(Yii::t('app', 'Terms of Use'), ['guest/default/terms-of-use'], ['target' => '_blank']) ?> and <?= Html::a(Yii::t('app', 'Privacy Policy'), ['guest/default/privacy-policy'], ['target' => '_blank']) ?>.
                    </p>
                </div>
            </div>

            <div class="card-footer">
                <div class="form-group">
                    <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
