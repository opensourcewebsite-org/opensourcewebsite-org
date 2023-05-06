<b>+<?= $walletTransaction->amount ?> <?= $walletTransaction->currency->code ?></b><br/>
<br/>
<?= Yii::t('bot', 'Sender') ?>: <?= $walletTransaction->fromUser->botUser->getFullLink() ?><br/>
<br/>
<?php if ($walletTransaction->hasTypeLabel()): ?>
<?= Yii::t('bot', 'Description') ?>: <?= $walletTransaction->getTypeLabel() ?><br/>
<br/>
<?php endif; ?>
<?= Yii::t('bot', 'Group') ?>: <b><?= $queue->chat->title ?></b><?= $queue->chat->username ? ' (@' . $queue->chat->username . ')' : '' ?><br/>
————<br/>
<i><?= Yii::t('bot', 'Available amount') ?>: <?= $toUserWallet->amount ?> <?= $walletTransaction->currency->code ?></i><br/>
