<?php

use app\widgets\buttons\Cancel;
use app\widgets\buttons\Save;
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
            <?php if ($model) : ?>
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
                    <?= Save::widget([
                        'text' => 'Change email',
                        'options' => ['name' => 'change-email-button']
                    ]); ?>
                    <?= Cancel::widget([
                        'url' => ['site/login']
                    ]); ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
            <?php else : ?>
            <div class="card-body">
                <div>
                    <p>
                        The link has been expired.
                    </p>
                </div>
            </div>

            <div class="card-footer">
                <div class="form-group">
                    <?= Html::a('Go back', ['site/login'], ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
