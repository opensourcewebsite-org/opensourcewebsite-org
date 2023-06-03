<b><?= Yii::t('bot', 'Send the amount for transfer') ?>:</b><br/>
<br/>
<?= Yii::t('bot', 'Receiver') ?>: <?= $walletTransaction->getReceiverLabel() ?><br/>
————<br/>
<i><?= Yii::t('bot', 'Available amount'); ?>: <?= $walletTransaction->fromUser->getWalletByCurrencyId($walletTransaction->getCurrencyId())->getAmountMinusFee() . ' ' . $walletTransaction->currency->code; ?></i><br/>
