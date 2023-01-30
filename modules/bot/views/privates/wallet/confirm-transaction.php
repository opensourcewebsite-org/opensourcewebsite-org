<?php

use app\models\WalletTransaction;

?>
<b><?= Yii::t('bot', 'Send transaction') ?></b><br/>
<br/>
<?= Yii::t('bot', 'Receiver') ?>: @<?= $walletTransaction->toUser->botUser->getUsername() ?><br/>
<?php if ($walletTransaction->amount) : ?>
<?= Yii::t('bot', 'Amount') ?>: <?= $walletTransaction->amount ?> <?= $walletTransaction->currency->code ?><br/>
<?= Yii::t('bot', 'Fee') ?>: <?= WalletTransaction::FEE ?> <?= $walletTransaction->currency->code ?><br/>
<?= Yii::t('bot', 'Total amount') ?>: <?= WalletTransaction::FEE + $walletTransaction->amount ?> <?= $walletTransaction->currency->code ?><br/>
<?php else : ?>
<?= Yii::t('bot', 'Enter amount of money to send') ?>:<br/>
<?php endif; ?>
