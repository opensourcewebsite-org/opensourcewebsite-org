<b><?= Yii::t('bot', 'Send amount for transfer') ?>:</b><br/>
<br/>
<i><?= Yii::t('bot', 'Available amount'); ?>: <?= $walletTransaction->fromUser->getWalletByCurrencyId($walletTransaction->getCurrencyId())->getAmountMinusFee() . ' ' . $walletTransaction->currency->code; ?></i><br/>
————<br/>
<?= Yii::t('bot', 'Receiver') ?>: <?= $walletTransaction->toUser->botUser->getFullLink() ?><br/>
