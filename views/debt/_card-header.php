<?php

use yii\helpers\Html;
use app\widgets\buttons\AddButton;

?>
<div class="card-header d-flex p-0">
    <div class="col-sm-6">
        <?= $this->render('_navbar', $_params_); ?>
    </div>
    <div class="col-sm-6">
        <?= AddButton::widget([
            'url' => ['create'],
            'options' => [
                'title' => Yii::t('app', 'New Debt'),
                'style' => [
                    'float' => 'right',
                ],
            ],
        ]); ?>
    </div>
</div>
