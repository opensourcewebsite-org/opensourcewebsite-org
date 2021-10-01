<?php

use yii\helpers\Html;

$this->title = 'Reset password';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="offset-md-2 col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            </div>
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
        </div>
    </div>
</div>
