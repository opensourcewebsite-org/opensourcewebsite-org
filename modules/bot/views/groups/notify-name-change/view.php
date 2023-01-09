<?php

use app\modules\bot\components\helpers\Emoji;

/**
 * @var $changedAttributes mixed
 * @var $user \app\modules\bot\models\User
 */
?>
<?php if (isset($changedAttributes->provider_user_name)) : ?>
<?php endif; ?>
<b><?= Yii::t('bot', 'User changed username'); ?></b>.<br/>
<?php if(isset($changedAttributes->provider_user_first_name) || isset($changedAttributes->provider_user_last_name)) : ?>
<b><?= Yii::t('bot', 'User changed name'); ?></b>.<br/>
<?php endif; ?>
<br/>
<b><?= Yii::t('bot', 'Telegram') ?> ID</b>: #<?= $user->getIdFullLink() ?><br/>
<b><?= Yii::t('bot', 'Username') ?></b>: <?= $user->provider_user_name ? ' @' . $user->provider_user_name : '(No Username)' ?> <?= isset($changedAttributes->provider_user_name) ? Emoji::RIGHT . ' ' . $changedAttributes->provider_user_name : '' ?><br/>
<?php if ($user->provider_user_first_name) : ?>
<b><?= Yii::t('bot', 'First Name') ?></b>: <?= $user->provider_user_first_name ?> <?= isset($changedAttributes->provider_user_first_name) ? Emoji::RIGHT . ' ' .  $changedAttributes->provider_user_first_name : ' '; ?><br/>
<?php endif; ?>
<?php if ($user->provider_user_last_name) : ?>
<b><?= Yii::t('bot', 'Last Name') ?></b>: <?= $user->provider_user_last_name ?> <?= isset($changedAttributes->provider_user_last_name) ? Emoji::RIGHT . ' ' .  $changedAttributes->provider_user_last_name : ' '; ?><br/>
<?php endif; ?>
<?php if ($globalUser = $user->globalUser) : ?>
<br/>
<b>OSW ID</b>: #<?= $globalUser->getIdFullLink() ?><?= ($globalUser->username ? ' @' . $globalUser->username : '') ?><br/>
<b><?= Yii::t('user', 'Rank') ?></b>: <?= $globalUser->getRank() ?><br/>
<b><?= Yii::t('user', 'Real confirmations') ?></b>: <?= $globalUser->getRealConfirmations() ?><br/>
<?php endif; ?>
