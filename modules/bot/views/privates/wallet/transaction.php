<?php

use app\models\WalletTransaction;

?>
<b><?= ($user->getUserId() == $walletTransaction->getFromUserID() ? '-' : '+') . $walletTransaction->getAmount() . ' ' . $walletTransaction->currency->code ?> - <?= Yii::$app->formatter->asDateTime($walletTransaction->getCreatedAtByUser()); ?></b><br/>
<br/>
<?= Yii::t('bot', 'Sender') ?>: <?= $walletTransaction->fromUser->botUser->getFullLink() ?><br/>
<br/>
<?= Yii::t('bot', 'Receiver') ?>: <?= $walletTransaction->getReceiverLabel() ?><br/>
<?php if ($walletTransaction->hasTypeLabel()) : ?>
<br/>
<?= Yii::t('bot', 'Description') ?>: <?= $walletTransaction->getTypeLabel() ?><br/>
<?php endif; ?>
<?php if ($user->getUserId() == $walletTransaction->getFromUserID()) : ?>
<br/>
<?= Yii::t('bot', 'Fee') ?>: <?= $walletTransaction->fee ?> <?= $walletTransaction->currency->code ?><br/>
<?php endif; ?>
