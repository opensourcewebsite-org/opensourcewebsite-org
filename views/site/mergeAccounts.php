<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

/* @var $model LoginForm */

use app\models\LoginForm;
use yii\bootstrap\ActiveForm;
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
            <? if ($model) { ?>
            <?php $form = ActiveForm::begin(['id' => 'merge-accounts-form']); ?>
            <div class="card-body">
                <div>
                    <p>
                        You are going to merge <?= $user->name ?>`s account with <?= $userToMerge->name?>` one.
                    </p>
                </div>
            </div>

            <div class="card-footer">
                <div class="form-group">
                    <?= Html::submitButton('Merge accounts', ['class' => 'btn btn-primary', 'name' => 'merge-accounts-button']) ?>
                    <?= Html::a('Cancel', ['site/login', 'id' => $id], ['class' => 'btn btn-default']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
            <? } else { ?>
            <div class="card-body">
                <div>
                    <p>
                        The link has expired.
                    </p>
                </div>
            </div>

            <div class="card-footer">
                <div class="form-group">
                    <?= Html::a('Go back', ['site/login', 'id' => $id], ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
            <? } ?>
        </div>
    </div>
</div>
