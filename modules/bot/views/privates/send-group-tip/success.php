<b><?= Yii::t('bot', 'Transaction') ?> <?= Yii::$app->formatter->asDateTime($walletTransaction->getCreatedAtByUser()); ?></b><br/>
<br/>
<?= Yii::t('bot', 'Receiver') ?>: @<?= $walletTransaction->toUser->botUser->getUsername() ?><br/>
<br/>
<b><?= Yii::t('bot', 'Total amount') ?>: <?= $walletTransaction->getAmountPlusFee() ?> <?= $walletTransaction->currency->code ?></b><br/>
  • <?= Yii::t('bot', 'Amount') ?>: <?= $walletTransaction->amount ?> <?= $walletTransaction->currency->code ?><br/>
  • <?= Yii::t('bot', 'Fee') ?>: <?= $walletTransaction->fee ?> <?= $walletTransaction->currency->code ?><br/>
