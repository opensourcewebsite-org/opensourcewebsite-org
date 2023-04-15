<b><?= Yii::t('bot', 'Transaction') ?> <?= Yii::$app->formatter->asDateTime($walletTransaction->getCreatedAtByUser()); ?></b><br/>
<br/>
<?= Yii::t('bot', 'Sender') ?>: <?= $walletTransaction->fromUser->botUser->getFullLink() ?><br/>
<?= Yii::t('bot', 'Receiver') ?>: <?= $walletTransaction->toUser->botUser->getFullLink() ?><br/>
<br/>
<?= Yii::t('bot', 'Amount') ?>: <?= $walletTransaction->getAmount() ?> <?= $walletTransaction->currency->code ?><br/>
<?= Yii::t('bot', 'Fee') ?>: <?= $walletTransaction->fee ?> <?= $walletTransaction->currency->code ?><br/>
