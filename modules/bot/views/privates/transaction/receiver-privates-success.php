<?php

use app\models\WalletTransaction;

?>
<b>+<?= $walletTransaction->amount ?> <?= $walletTransaction->currency->code ?></b><br/>
<br/>
<?= Yii::t('bot', 'Sender') ?>: <?= $walletTransaction->fromUser->botUser->getFullLink() ?><br/>
<br/>
<?php if ($walletTransaction->hasTypeLabel()): ?>
<?= Yii::t('bot', 'Description') ?>: <?= $walletTransaction->getTypeLabel() ?><br/>
<br/>
<?php endif; ?>
<?php switch($walletTransaction->type): ?>
<?php case WalletTransaction::MEMBERSHIP_PAYMENT_TYPE: ?>
<?php if (isset($chatMember->id)): ?>
<?= Yii::t('bot', 'Group') ?>: <b><?= $chatMember->chat->title ?></b><?= $chatMember->chat->username ? ' (@' . $chatMember->chat->username . ')' : '' ?><br/>
<?php endif; ?>
<?php break; ?>
<?php default: ?>
<?php if (isset($chatTip->id)): ?>
<?= Yii::t('bot', 'Group') ?>: <b><?= $chatTip->chat->title ?></b><?= $chatTip->chat->username ? ' (@' . $chatTip->chat->username . ')' : '' ?><br/>
<?php endif; ?>
<?php endswitch; ?>
————<br/>
<i><?= Yii::t('bot', 'Available amount') ?>: <?= $toUserWallet->amount ?> <?= $walletTransaction->currency->code ?></i><br/>
