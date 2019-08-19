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
<div class="row">
    <div class="offset-md-2 col-md-8">
        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
        <div class="card-body">
            <p>Please fill out the following fields to login:</p>
            <div class="form-group">
                <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>
            </div>
            <div class="form-group">
                <?= $form->field($model, 'password')->passwordInput() ?>
            </div>
            <div class="form-group">
                <?= $form->field($model, 'rememberMe')->checkbox() ?>
            </div>

            <div style="color:#999;margin:1em 0">
                If you forgot your password you can 
                <?= Html::a('reset it', '#', [
                    'onclick' => '$.get("' . Yii::$app->urlManager->createUrl(['site/request-password-reset']) .'", {}, function (result){
                        $("#main-modal-body").html(result);
                        $("#main-modal-header").html("' . Yii::t('app', 'Request password reset') . '").data("target", "' . Yii::$app->urlManager->createUrl(['site/request-password-reset']) . '");
                        $("#main-modal").modal("show");
                    })'
                ]) ?>.
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>