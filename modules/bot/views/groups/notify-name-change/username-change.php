<?php
/**
 * @var $updateUser \TelegramBot\Api\Types\User
 * @var $oldUser \app\modules\bot\models\User
 */
?>

<?= Yii::t('bot', 'User') ?> <?= $updateUser->getId(); ?>
<?= Yii::t('bot', ' changed username from') ?> <?= $oldUser->getUsername(); ?>
<?= Yii::t('bot', ' to') ?> <?= $updateUser->getUsername(); ?>