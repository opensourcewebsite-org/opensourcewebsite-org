<b><?= Yii::t('bot', 'Check transfer details') ?></b><br/>
<br/>
<?= Yii::t('bot', 'Receiver') ?>: @<?= $walletTransaction->toUser->botUser->getUsername() ?><br/>
<br/>
<?php if ($walletTransaction->amount && $walletTransaction->currency->code) : ?>
<?= Yii::t('bot', 'Total amount') ?>: <?= $walletTransaction->getAmountPlusFee() ?> <?= $walletTransaction->currency->code ?><br/>
  • <?= Yii::t('bot', 'Amount') ?>: <?= $walletTransaction->amount ?> <?= $walletTransaction->currency->code ?><br/>
  • <?= Yii::t('bot', 'Fee') ?>: <?= $walletTransaction->fee ?> <?= $walletTransaction->currency->code ?><br/>
<?php endif; ?>
