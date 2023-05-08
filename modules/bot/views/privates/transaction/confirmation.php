<b><?= Yii::t('bot', 'Transfer confirmation') ?></b><br/>
<?php if ($walletTransaction->hasTypeLabel()): ?>
<?= Yii::t('bot', 'Description') ?>: <?= $walletTransaction->getTypeLabel() ?><br/>
<br/>
<?php endif; ?>
<?php if (!$walletTransaction->anonymity): ?>
<br/>
<?= Yii::t('bot', 'Receiver') ?>: <?= $walletTransaction->getReceiverLabel() ?><br/>
<?php endif; ?>
<?php if ($walletTransaction->hasGroupLabel()): ?>
<br/>
<?= Yii::t('bot', 'Group') ?>: <?= $walletTransaction->getGroupLabel() ?><br/>
<?php endif; ?>
<br/>
<b><?= Yii::t('bot', 'Total amount') ?>: <?= $walletTransaction->getAmountPlusFee() ?> <?= $walletTransaction->currency->code ?></b><br/>
  • <?= Yii::t('bot', 'Amount') ?>: <?= $walletTransaction->amount ?> <?= $walletTransaction->currency->code ?><br/>
  • <?= Yii::t('bot', 'Fee') ?>: <?= $walletTransaction->fee ?> <?= $walletTransaction->currency->code ?><br/>
