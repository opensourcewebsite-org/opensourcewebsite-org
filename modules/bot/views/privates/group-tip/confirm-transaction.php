<?php

use app\models\WalletTransaction;

?>
<b><?= Yii::t('bot', 'Transaction') ?></b><br/><br/>
<?= Yii::t('bot', 'Sender') ?>: @<?= $fromUser->getUsername() ?><br/>
<?= Yii::t('bot', 'Receiver') ?>: @<?= $toUser->getUsername() ?><br/>
<?php if ($amount && $code) : ?>
<?= Yii::t('bot', 'Amount') ?>: <?= $amount ?> <?= $code ?><br/>
<?= Yii::t('bot', 'Fee') ?>: <?= WalletTransaction::TRANSACTION_FEE ?> <?= $code ?><br/>
<?= Yii::t('bot', 'Total amount') ?>: <?= WalletTransaction::TRANSACTION_FEE + $amount ?> <?= $code ?><br/>
<?php endif; ?>