<?php

use app\models\WalletTransaction;

?>
<b><?= Yii::t('bot', 'Transaction') ?></b><br/>
————————————————————<br/>
<?= Yii::t('bot', 'Receiver') ?>: @<?= $toUser->getUsername() ?><br/>
<?php if ($amount && $code) : ?>
<?= Yii::t('bot', 'Amount') ?>: <?= $amount ?> <?= $code ?><br/>
<?= Yii::t('bot', 'Fee') ?>: <?= WalletTransaction::FEE ?> <?= $code ?><br/>
<?= Yii::t('bot', 'Total amount') ?>: <?= WalletTransaction::FEE + $amount ?> <?= $code ?><br/>
<?php endif; ?>