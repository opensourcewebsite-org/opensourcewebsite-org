<b><?= Yii::t('bot', 'Not enough money in your wallet') ?></b><br/>
<br/>
<?= Yii::t('bot', 'User') ?>: <?= $chatMember->user->getFullLink() ?><br/>
<br/>
<b><?= Yii::t('bot', 'Group') ?>: <?= $chatMember->chat->title ?></b><?= $chatMember->chat->username ? ' (@' . $chatMember->chat->username . ')' : '' ?><br/>
<br/>
<?= Yii::t('bot', 'Currency') ?>: <?= $chatMember->chat->currency->code ?><br/>
————<br />
<i><?= Yii::t('bot', 'Available amount') ?>: <?= $chatMember->user->getWalletByCurrencyId($chatMember->chat->currency->id)->getAmountMinusFee() . ' ' . $chatMember->chat->currency->code ?></i><br />
