<?php
/**
 * @var $updateUser \TelegramBot\Api\Types\User
 * @var $oldUser \app\modules\bot\models\User
 */
?>

<?= Yii::t('bot', 'User') ?> <?= $updateUser->getId(); ?>
<?= Yii::t('bot', ' changed name from') ?> <?= $oldUser->provider_user_first_name; ?> <?= $oldUser->provider_user_last_name ?? ''; ?>
<?= Yii::t('bot', ' to') ?> <?= $updateUser->getFirstName(); ?> <?= $updateUser->getLastName() ?? ''; ?>