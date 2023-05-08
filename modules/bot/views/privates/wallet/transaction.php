<b><?= ($user->getUserId() == $walletTransaction->getFromUserID() ? '-' : '+') . $walletTransaction->getAmount() . ' ' . $walletTransaction->currency->code ?> - <?= Yii::$app->formatter->asDateTime($walletTransaction->getCreatedAtByUser()); ?></b><br/>
<?php if ($walletTransaction->hasTypeLabel()) : ?>
<br/>
<?= Yii::t('bot', 'Description') ?>: <?= $walletTransaction->getTypeLabel() ?><br/>
<?php endif; ?>
<br/>
<?= Yii::t('bot', 'Sender') ?>: <?= $walletTransaction->fromUser->botUser->getFullLink() ?><br/>
<?php if (!$walletTransaction->anonymity): ?>
<br/>
<?= Yii::t('bot', 'Receiver') ?>: <?= $walletTransaction->getReceiverLabel() ?><br/>
<?php endif; ?>
<?php if ($walletTransaction->hasGroupLabel()): ?>
<br/>
<?= Yii::t('bot', 'Group') ?>: <?= $walletTransaction->getGroupLabel() ?><br/>
<?php endif; ?>
<?php if ($user->getUserId() == $walletTransaction->getFromUserID()) : ?>
<br/>
<?= Yii::t('bot', 'Fee') ?>: <?= $walletTransaction->fee ?> <?= $walletTransaction->currency->code ?><br/>
<?php endif; ?>
