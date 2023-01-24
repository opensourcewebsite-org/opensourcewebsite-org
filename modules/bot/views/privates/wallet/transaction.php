<?= date('Y-m-d H:i:s', $walletTransaction->getCreatedAt()); ?><br/>
<br/>
<?= Yii::t('bot', 'Sender') ?>: @<?= $fromUser->getUsername() ?><br/>
<?= Yii::t('bot', 'Receiver') ?>: @<?= $toUser->getUsername() ?><br/>
<?= Yii::t('bot', 'Amount') ?>: <?= $walletTransaction->getAmount() ?> <?= $currency->code ?><br/>
<?= Yii::t('bot', 'Type') ?>: <?= $walletTransaction->getType() ?><br/>
<?= Yii::t('bot', 'Anonymity') ?>: <?= $walletTransaction->getAnonymity() ?>
