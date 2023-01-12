<?= date('Y-m-d H:i:s', $transaction->getCreatedAt()); ?><br/>
<br/>
<?= Yii::t('bot', 'Sender') ?>: @<?= $fromUser->getUsername() ?><br/>
<?= Yii::t('bot', 'Receiver') ?>: @<?= $toUser->getUsername() ?><br/>
<?= Yii::t('bot', 'Amount') ?>: <?= $transaction->getAmount() ?> <?= $currency->code ?><br/>
<?= Yii::t('bot', 'Type') ?>: <?= $transaction->getType() ?><br/>
<?= Yii::t('bot', 'Anonymity') ?>: <?= $transaction->getAnonymity() ?>
