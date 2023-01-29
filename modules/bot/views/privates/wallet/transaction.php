<?= date('Y-m-d H:i:s', $walletTransaction->getCreatedAt()); ?><br/>
<br/>
<?= Yii::t('bot', 'Sender') ?>: @<?= $walletTransaction->fromUser->botUser->getUsername() ?><br/>
<?= Yii::t('bot', 'Receiver') ?>: @<?= $walletTransaction->toUser->botUser->getUsername() ?><br/>
<?= Yii::t('bot', 'Amount') ?>: <?= $walletTransaction->getAmount() ?> <?= $walletTransaction->currency->code ?><br/>
<?= Yii::t('bot', 'Type') ?>: <?= $walletTransaction->getType() ?><br/>
<?= Yii::t('bot', 'Anonymity') ?>: <?= $walletTransaction->getAnonymity() ?>
