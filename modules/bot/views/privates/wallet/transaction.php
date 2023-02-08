<b><?= Yii::t('bot', 'Transaction') ?> <?= $walletTransaction->getCreatedAtByTimezone($timezone); ?></b><br/>
<br/>
<?= Yii::t('bot', 'Sender') ?>: @<?= $walletTransaction->fromUser->botUser->getUsername() ?><br/>
<?= Yii::t('bot', 'Receiver') ?>: @<?= $walletTransaction->toUser->botUser->getUsername() ?><br/>
<br/>
<?= Yii::t('bot', 'Amount') ?>: <?= $walletTransaction->getAmount() ?> <?= $walletTransaction->currency->code ?><br/>
<?= Yii::t('bot', 'Fee') ?>: <?= $walletTransaction->fee ?> <?= $walletTransaction->currency->code ?><br/>
