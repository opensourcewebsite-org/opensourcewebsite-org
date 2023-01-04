<?php

use app\modules\bot\components\helpers\Emoji;

/**
 * @var $updateUser \TelegramBot\Api\Types\User
 * @var $oldUser \app\modules\bot\models\User
 */
?>
<b><?= Yii::t('bot', 'User changed name'); ?></b>.<br/>
<br/>
<b><?= Yii::t('bot', 'Telegram') ?> ID</b>: #<?= $user->getIdFullLink() ?> <?= $user->provider_user_name ? ' @' . $user->provider_user_name : '' ?><br/>
<?php if ($user->provider_user_first_name) : ?>
<b><?= Yii::t('bot', 'First Name') ?></b>: <?= $user->provider_user_first_name ?> <?= Emoji::RIGHT ?> <?= $updateUser->getFirstName() ?><br/>
<?php endif; ?>
<?php if ($user->provider_user_last_name) : ?>
<b><?= Yii::t('bot', 'Last Name') ?></b>: <?= $user->provider_user_last_name ?> <?= Emoji::RIGHT ?> <?= $updateUser->getLastName() ?><br/>
<?php endif; ?>
<?php if ($globalUser = $user->globalUser) : ?>
<br/>
<b>OSW ID</b>: #<?= $globalUser->getIdFullLink() ?><?= ($globalUser->username ? ' @' . $globalUser->username : '') ?><br/>
<b><?= Yii::t('user', 'Rank') ?></b>: <?= $globalUser->getRank() ?><br/>
<b><?= Yii::t('user', 'Real confirmations') ?></b>: <?= $globalUser->getRealConfirmations() ?><br/>
<?php endif; ?>
