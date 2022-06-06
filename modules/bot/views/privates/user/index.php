<?php

use app\components\helpers\Html;

// TODO add link to user website profile
?>
<?php if ($globalUser = $user->globalUser) : ?>
<b><?= $globalUser ? $globalUser->getDisplayName() : '' ?></b><br/>
<br/>
<b>ID</b>: #<?= $globalUser->id; ?><?= ($globalUser->username ? ' @' . $globalUser->username : '') ?><br/>
<br/>
<b><?= Yii::t('user', 'Rank') ?></b>: <?= $globalUser->getRank(); ?><br/>
<b><?= Yii::t('user', 'Real confirmations') ?></b>: <?= $globalUser->getRealConfirmations(); ?><br/>
<br/>
<?php endif; ?>
<b><?= Yii::t('bot', 'Telegram') ?> ID</b>: #<?= $user->provider_user_id; ?><?= ($user->provider_user_name ? ' @' . $user->provider_user_name : '') ?><br/>
<?php if ($user->provider_user_first_name) : ?>
<b><?= Yii::t('bot', 'First Name') ?></b>: <?= $user->provider_user_first_name; ?><br/>
<?php endif; ?>
<?php if ($user->provider_user_last_name) : ?>
<b><?= Yii::t('bot', 'Last Name') ?></b>: <?= $user->provider_user_last_name; ?><br/>
<?php endif; ?>
