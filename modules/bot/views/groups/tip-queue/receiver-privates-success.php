<b>+<?= $walletTransaction->amount ?> <?= $walletTransaction->currency->code ?></b><br/>
<br/>
<?= Yii::t('bot', 'Sender') ?>: <?= $walletTransaction->fromUser->botUser->getFullLink() ?><br/>
<?php if ($walletTransaction->hasTypeLabel()): ?>
<br/>
<?= Yii::t('bot', 'Description') ?>: <?= $walletTransaction->getTypeLabel() ?><br/>
<?php endif; ?>
<br/>
<?= Yii::t('bot', 'Group') ?>: <?= $queue->chat->title ?><?= $queue->chat->username ? ' (@' . $queue->chat->username . ')' : '' ?><br/>
————<br/>
<i><?= Yii::t('bot', 'Available amount') ?>: <?= $toUserWallet->amount ?> <?= $walletTransaction->currency->code ?></i><br/>
