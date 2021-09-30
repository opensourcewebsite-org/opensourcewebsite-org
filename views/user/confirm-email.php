<?php

use yii\bootstrap4\ActiveForm;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use yii\helpers\Html;

$this->title = 'Confirm email';
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Account'), 'url' => ['account']];
?>
<div class="row">
    <div class="offset-md-2 col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            </div>
            <?php $form = ActiveForm::begin(['id' => 'confirm-email-form']); ?>
            <div class="card-body">
                <div>
                    <p>
                        Please confirm your email: <?= $user->email->email ?>.
                    </p>
                </div>
            </div>
            <div class="card-footer">
                <div class="form-group">
                    <?= SaveButton::widget([
                        'text' => 'Confirm',
                        'options' => [
                            'name' => 'confirm-email-button',
                            'title' => 'Confirm',
                        ],
                    ]); ?>
                    <?= CancelButton::widget([
                        'url' => [
                            'site/login',
                        ],
                    ]); ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
