<b><?= Yii::t('bot', 'Transfer confirmation') ?></b><br/>
<br/>
<?= Yii::t('bot', 'Receiver') ?>: <?= $walletTransaction->toUser->botUser->getFullLink() ?><br/>
<?= Yii::t('bot', 'Total amount') ?>: <?= $walletTransaction->getAmountPlusFee() ?> <?= $walletTransaction->currency->code ?><br/>
  • <?= Yii::t('bot', 'Amount') ?>: <?= $walletTransaction->amount ?> <?= $walletTransaction->currency->code ?><br/>
  • <?= Yii::t('bot', 'Fee') ?>: <?= $walletTransaction->fee ?> <?= $walletTransaction->currency->code ?><br/>