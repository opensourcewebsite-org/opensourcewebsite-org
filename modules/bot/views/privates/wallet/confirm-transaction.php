<?php

use app\models\WalletTransaction;

?>
<b><?= Yii::t('bot', 'Send transaction') ?></b><br/>
<br/>
<?= Yii::t('bot', 'Receiver') ?>: @<?= $toUser->getUsername() ?><br/>
<?php if ($amount) : ?>
<?= Yii::t('bot', 'Amount') ?>: <?= $amount; ?> <?= $currency->code; ?><br/>
<?= Yii::t('bot', 'Fee') ?>: <?= WalletTransaction::FEE; ?> <?= $currency->code; ?><br/>
<?= Yii::t('bot', 'Total amount') ?>: <?= WalletTransaction::FEE + $amount; ?> <?= $currency->code; ?><br/>
<?php else : ?>
<?= Yii::t('bot', 'Enter amount of money to send') ?>:<br/>
<?php endif; ?>
