<?php

use app\models\WalletTransaction;

?>
<b><?= Yii::t('bot', 'Transfer confirmation') ?></b><br/>
<br/>
<?= Yii::t('bot', 'Receiver') ?>: 
<?php if ($walletTransaction->anonymity): ?>
<?php if ($walletTransaction->type == WalletTransaction::SEND_ANONYMOUS_ADMIN_TIP_TYPE && !empty($chatTip->id)): ?>
<b><?= $chatTip->chat->title ?></b><?= $chatTip->chat->username ? ' (@' . $chatTip->chat->username . ')' : '' ?><br/>
<?php endif; ?>
<?php else: ?>
<?= $walletTransaction->toUser->botUser->getFullLink() ?><br />
<?php endif; ?>
<br />
<?php if ($walletTransaction->hasTypeLabel()): ?>
<?= Yii::t('bot', 'Description') ?>: <?= $walletTransaction->getTypeLabel() ?><br/>
<br/>
<?php endif; ?>
<?= Yii::t('bot', 'Total amount') ?>: <?= $walletTransaction->getAmountPlusFee() ?> <?= $walletTransaction->currency->code ?><br/>
  • <?= Yii::t('bot', 'Amount') ?>: <?= $walletTransaction->amount ?> <?= $walletTransaction->currency->code ?><br/>
  • <?= Yii::t('bot', 'Fee') ?>: <?= $walletTransaction->fee ?> <?= $walletTransaction->currency->code ?><br/>
