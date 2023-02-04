<b><?= Yii::t('bot', 'You have received a transfer. ') ?></b><br/><br/>
<?= Yii::t('bot', 'Sender') ?>: @<?= $walletTransaction->fromUser->botUser->getUsername() ?><br/>
<?= Yii::t('bot', 'Receiver') ?>: @<?= $walletTransaction->toUser->botUser->getUsername() ?><br/><br/>
<?= Yii::t('bot', 'Current balance:') ?>: <?= $toUserWallet->amount ?> <?= $walletTransaction->currency->code ?><br/>