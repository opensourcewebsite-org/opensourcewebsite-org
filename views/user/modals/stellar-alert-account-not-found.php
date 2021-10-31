<?php

declare(strict_types=1);

use yii\web\View;
use app\widgets\Modal;
use yii\helpers\Html;

?>
<?php Modal::begin([
    'id' => 'modal',
    'size' => Modal::SIZE_LARGE,
    'options' => ['class' => 'card-primary', 'tabindex' => false],
    'title' => Html::tag('h4', '', ['id' => 'modal-header', 'class' => 'modal-title']),
    'titleOptions' => ['class' => 'card-header'],
    'bodyOptions' => ['id' => 'modal-body'],
]);
?>
<div>
<?= Yii::t('bot', 'Stellar account with this public key was not found, please use a different public key'); ?>.
</div>
<?php Modal::end(); ?>
