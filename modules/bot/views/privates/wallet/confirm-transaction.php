<?php

use app\models\WalletTransaction;

?>
<b><?= Yii::t('bot', 'Send transaction') ?></b><br/>
<br/>
<?= Yii::t('bot', 'Receiver') ?>: <?= $walletTransaction->toUser->botUser->getFullLink() ?><br/>
<?php if ($walletTransaction->amount) : ?>
<?= Yii::t('bot', 'Amount') ?>: <?= $walletTransaction->amount ?> <?= $walletTransaction->currency->code ?><br/>
<?= Yii::t('bot', 'Fee') ?>: <?= WalletTransaction::FEE ?> <?= $walletTransaction->currency->code ?><br/>
<?= Yii::t('bot', 'Total amount') ?>: <?= $walletTransaction->getAmountPlusFee() ?> <?= $walletTransaction->currency->code ?><br/>
<?php else : ?>
<?= Yii::t('bot', 'Enter amount of money to send') ?>:<br/>
————<br/>
<i><?= Yii::t('bot', 'Available amount'); ?>: <?= $walletTransaction->fromUser->getWalletByCurrencyId(@$walletTransaction->currency->id)->getAmountMinusFee() . ' ' . @$walletTransaction->currency->code; ?></i><br/>
<?php endif; ?>
