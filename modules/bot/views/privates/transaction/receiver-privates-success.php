<b>+<?= $walletTransaction->amount ?> <?= $walletTransaction->currency->code ?></b><br/>
<?= Yii::t('bot', 'Description') ?>: <?= $walletTransaction->getTypeLabel() ?><br/>
<?= Yii::t('bot', 'Sender') ?>: <?= $walletTransaction->fromUser->botUser->getFullLink() ?><br/>
<?php if ($chatTip): ?>
<?= Yii::t('bot', 'Group') ?>: <b><?= $chatTip->chat->title ?></b><?= $chatTip->chat->username ? ' (@' . $chatTip->chat->username . ')' : '' ?><br/>
<?php endif; ?>
————<br/>
<i><?= Yii::t('bot', 'Available amount') ?>: <?= $toUserWallet->amount ?> <?= $walletTransaction->currency->code ?></i><br/>
