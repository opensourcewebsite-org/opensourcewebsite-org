<?php

use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;

$this->title = 'Merge accounts';
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
            <?php $form = ActiveForm::begin(['id' => 'merge-accounts-form']); ?>
            <div class="card-body">
                <div>
                    <p>
                        You are going to merge <?= !empty($user->name) ? $user->name : 'noname' ?>`s account with <?= !empty($userToMerge->name) ? $userToMerge->name : 'noname' ?>` one.
                    </p>
                </div>
            </div>

            <div class="card-footer">
                <div class="form-group">
                    <?= Html::submitButton('Merge accounts', ['class' => 'btn btn-primary', 'name' => 'merge-accounts-button']) ?>
                    <?= Html::a('Cancel', ['site/login'], ['class' => 'btn btn-default']) ?>
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
