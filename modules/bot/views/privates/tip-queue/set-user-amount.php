<b><?= Yii::t('bot', 'Send the amount for one gift') ?>:</b><br/>
————<br/>
<i><?= Yii::t('bot', 'Available amount') ?>: <?= $chatTipQueue->user->globalUser->getWalletByCurrencyId($chatTipQueue->getCurrencyId())->getAmountMinusFee() . ' ' . $chatTipQueue->currency->code; ?></i><br/>
