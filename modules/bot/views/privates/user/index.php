<?php

use app\components\helpers\Html;

// TODO add link to user website profile
?>
<?php if ($user) : ?>
<b><?= $user ? $user->getDisplayName() : '' ?></b><br/>
<br/>
<b>ID</b>: #<?= $user->id; ?><?= ($user->username ? ' @' . $user->username : '') ?><br/>
<br/>
<b><?= Yii::t('user', 'Rank') ?></b>: <?= $user->getRank(); ?><br/>
<b><?= Yii::t('user', 'Real confirmations') ?></b>: <?= $user->getRealConfirmations(); ?><br/>
<br/>
<?php endif; ?>
<b><?= Yii::t('bot', 'Telegram') ?> ID</b>: #<?= $telegramUser->provider_user_id; ?><?= ($telegramUser->provider_user_name ? ' @' . $telegramUser->provider_user_name : '') ?><br/>
<?php if ($telegramUser->provider_user_first_name) : ?>
<b><?= Yii::t('bot', 'First Name') ?></b>: <?= $telegramUser->provider_user_first_name; ?><br/>
<?php endif; ?>
<?php if ($telegramUser->provider_user_last_name) : ?>
<b><?= Yii::t('bot', 'Last Name') ?></b>: <?= $telegramUser->provider_user_last_name; ?><br/>
<?php endif; ?>
