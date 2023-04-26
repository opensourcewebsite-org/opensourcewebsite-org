<?php

use app\models\WalletTransaction;

?>
<b><?= Yii::t('bot', 'Send amount for transfer') ?>:</b><br />
<br />
<?= Yii::t('bot', 'Receiver') ?>: 
<?php if ($walletTransaction->anonymity): ?>
<?php if ($walletTransaction->type == WalletTransaction::SEND_ANONYMOUS_ADMIN_TIP_TYPE && !empty($chatTip->id)): ?>
<b><?= $chatTip->chat->title ?></b><?= $chatTip->chat->username ? ' (@' . $chatTip->chat->username . ')' : '' ?><br/>
<?php endif; ?>
<?php else: ?>
<?= $walletTransaction->toUser->botUser->getFullLink() ?><br />
<?php endif; ?>
————<br />
<i><?= Yii::t('bot', 'Available amount'); ?>:
<?= $walletTransaction->fromUser->getWalletByCurrencyId($walletTransaction->getCurrencyId())->getAmountMinusFee() . ' ' . $walletTransaction->currency->code; ?></i><br />