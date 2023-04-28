<b><?= Yii::t('bot', 'Set user amount') ?>:</b><br/>
————<br />
<i><?= Yii::t('bot', 'Available amount'); ?>:
<?= $chatTipQueue->user->globalUser->getWalletByCurrencyId($chatTipQueue->getCurrencyId())->getAmountMinusFee() . ' ' . $chatTipQueue->currency->code; ?></i><br />
