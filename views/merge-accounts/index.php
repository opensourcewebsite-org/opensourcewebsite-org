<?php

use yii\widgets\ActiveForm;
use app\components\helpers\Html;
use app\widgets\buttons\SaveButton;
use yii\captcha\Captcha;

$this->title = 'Merge accounts';

$form = ActiveForm::begin(['id' => 'form']);
?>
<div class="form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= Html::icon('warning') ?> Attention! This operation is irreversible.<br />
                            <br />
                            Enter the credentials of source account that will be merged with your account. All objects and operations of source account will be moved to your account.<br />
                            <br />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'password')->passwordInput() ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'captcha')->widget(Captcha::className()) ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget([
                        'text' => Yii::t('app', 'Merge'),
                        'options' => [
                            'title' => Yii::t('app', 'Merge'),
                        ]
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
