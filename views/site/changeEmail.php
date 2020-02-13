<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = 'Change email';
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
            <? if ($model) : ?>
            <?php $form = ActiveForm::begin(['id' => 'change-email-form']); ?>
            <div class="card-body">
                <div>
                    <p>
                        Please confirm changing your email to <?= $model->email ?>.
                    </p>
                </div>
            </div>

            <div class="card-footer">
                <div class="form-group">
                    <?= Html::submitButton('Change email', ['class' => 'btn btn-primary', 'name' => 'change-email-button']) ?>
                    <?= Html::a('Cancel', ['site/login', 'id' => $id], ['class' => 'btn btn-default']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
            <? else : ?>
            <div class="card-body">
                <div>
                    <p>
                        The link has been expired.
                    </p>
                </div>
            </div>

            <div class="card-footer">
                <div class="form-group">
                    <?= Html::a('Go back', ['site/login', 'id' => $id], ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
            <? endif; ?>
        </div>
    </div>
</div>
