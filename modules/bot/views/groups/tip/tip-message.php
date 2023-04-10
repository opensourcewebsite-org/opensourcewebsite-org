<?php

use app\modules\bot\components\helpers\Emoji;

?>
<b><?= Yii::t('bot', 'Telegram') ?> ID</b>: #<?= $user->getIdFullLink() ?><?= ($user->provider_user_name ? ' @' . $user->provider_user_name : '') ?><br/>
<?php if ($user->provider_user_first_name) : ?>
<b><?= Yii::t('bot', 'First Name') ?></b>: <?= $user->provider_user_first_name ?><br/>
<?php endif; ?>
<?php if ($user->provider_user_last_name) : ?>
<b><?= Yii::t('bot', 'Last Name') ?></b>: <?= $user->provider_user_last_name ?><br/>
<?php endif; ?>
<?php if ($globalUser = $user->globalUser) : ?>
<br/>
<b>OSW ID</b>: #<?= $globalUser->getIdFullLink() ?><?= ($globalUser->username ? ' @' . $globalUser->username : '') ?><br/>
<b><?= Yii::t('user', 'Rank') ?></b>: <?= $globalUser->getRank() ?><br/>
<b><?= Yii::t('user', 'Real confirmations') ?></b>: <?= $globalUser->getRealConfirmations() ?><br/>
<?php endif; ?>
————<br/>
<?= Emoji::GIFT ?> <b><?= Yii::t('bot', 'Tips') ?></b>:<br/>
<br/>
<?php foreach($totalAmounts as $code => $amount) : ?>
  • <?= $amount ?> <?= $code ?><br/>
<?php endforeach; ?>
