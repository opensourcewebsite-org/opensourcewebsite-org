<?php

use app\models\User;

?>
<?php if ($user) : ?>
<b>OSW ID</b>: #<?= $user->id; ?><?= ($user->username ? ' @' . $user->username : '') ?><br/>
<br/>
<?php endif; ?>
<b><?= Yii::t('bot', 'Telegram') ?> ID</b>: #<?= $telegramUser->provider_user_id; ?><?= ($telegramUser->provider_user_name ? ' @' . $telegramUser->provider_user_name : '') ?><br/>
