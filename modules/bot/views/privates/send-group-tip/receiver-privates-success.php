<b><?= Yii::t('bot', 'Transfer received') ?></b><br/>
<br/>
<?= Yii::t('bot', 'Sender') ?>: @<?= $walletTransaction->fromUser->botUser->getUsername() ?><br/>
<br/>
<?= Yii::t('bot', 'Amount') ?>: <?= $walletTransaction->amount ?> <?= $walletTransaction->currency->code ?><br/>
————<br/>
<i><?= Yii::t('bot', 'Available amount') ?>: <?= $toUserWallet->amount ?> <?= $walletTransaction->currency->code ?></i><br/>
