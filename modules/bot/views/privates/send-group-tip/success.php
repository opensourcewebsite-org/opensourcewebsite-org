<?php

use app\models\WalletTransaction;

?>

<b><?= Yii::t('bot', 'Transaction was successfully sent') ?></b><br/><br/>
<b><?= Yii::t('bot', 'Info') ?></b><br/>
————————————————————<br/>
<?= Yii::t('bot', 'Receiver') ?>: @<?= $walletTransaction->toUser->botUser->getUsername() ?><br/>
<?= Yii::t('bot', 'Amount') ?>: <?= $walletTransaction->amount ?> <?= $walletTransaction->currency->code ?><br/>
<?= Yii::t('bot', 'Fee') ?>: <?= WalletTransaction::FEE ?> <?= $walletTransaction->currency->code ?><br/>
<?= Yii::t('bot', 'Total amount') ?>: <?= WalletTransaction::FEE + $walletTransaction->amount ?> <?= $walletTransaction->currency->code ?><br/>