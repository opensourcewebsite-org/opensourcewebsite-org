<?php

use app\components\helpers\Html;

// TODO add link to user website profile
?>
<b><?= $user->getDisplayName() ?></b><br/>
<br/>
User ID: <?= $user->id; ?><br/>
<?php if ($user->username) : ?>
<?= Yii::t('app', 'Username') . ': @' . $user->username; ?><br/>
<?php endif; ?>
<?= Yii::t('user', 'Rank') . ': ' . $user->getRank(); ?><br/>
<?= Yii::t('user', 'Real confirmations') . ': ' . $user->getRealConfirmations(); ?><br/>
<br/>
<?= Yii::t('bot', 'Telegram') . ' User ID: ' . $telegramUser->provider_user_id; ?><br/>
<?php if ($telegramUser->provider_user_name) : ?>
<?= Yii::t('bot', 'Telegram Username') . ': @' . $telegramUser->provider_user_name; ?><br/>
<?php endif; ?>
<?php if ($telegramUser->provider_user_first_name) : ?>
<?= Yii::t('bot', 'First Name') . ': ' . $telegramUser->provider_user_first_name; ?><br/>
<?php endif; ?>
<?php if ($telegramUser->provider_user_last_name) : ?>
<?= Yii::t('bot', 'Last Name') . ': ' . $telegramUser->provider_user_last_name; ?><br/>
<?php endif; ?>
