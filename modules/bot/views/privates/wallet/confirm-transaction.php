<b><?= Yii::t('bot', 'Send transaction') ?></b><br/>
<br/>
<?= Yii::t('bot', 'Sender') ?>: @<?= $fromUser->getUsername() ?><br/>
<?= Yii::t('bot', 'Receiver') ?>: @<?= $toUser->getUsername() ?><br/>
<?php if ($amount) : ?>
<?= Yii::t('bot', 'Amount') ?>: <?= $amount; ?> <?= $currency->code; ?><br/>
<?= Yii::t('bot', 'Fee') ?>: <?= $fee; ?> <?= $currency->code; ?><br/>
<?= Yii::t('bot', 'Total amount') ?>: <?= $fee + $amount; ?> <?= $currency->code; ?><br/>
<?php endif; ?>
<br/>
<?= Yii::t('bot', 'Send amount of money to send') ?>:<br/>
