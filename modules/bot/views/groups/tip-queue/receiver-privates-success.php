<b>+<?= $walletTransaction->amount ?> <?= $walletTransaction->currency->code ?></b><br/>
<?= Yii::t('bot', 'Description') ?>: <?= $walletTransaction->getTypeLabel() ?><br/>
<?= Yii::t('bot', 'Sender') ?>: <?= $walletTransaction->fromUser->botUser->getFullLink() ?><br/>
<?= Yii::t('bot', 'Group') ?>: <b><?= $queue->chat->title ?></b><?= $queue->chat->username ? ' (@' . $queue->chat->username . ')' : '' ?><br/>
————<br/>
<i><?= Yii::t('bot', 'Available amount') ?>: <?= $toUserWallet->amount ?> <?= $walletTransaction->currency->code ?></i><br/>
