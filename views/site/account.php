<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
$this->title = 'Account';
?>

<div class="user-profile-form">
    <?php echo DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'email',
            'rating'
        ],
    ]) ?>
</div>
