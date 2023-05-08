<b><?= Yii::t('bot', 'Group') ?>: <?= $chatMember->chat->title ?></b><?= $chatMember->chat->username ? ' (@' . $chatMember->chat->username . ')' : '' ?><br/>
<br/>
<?= Yii::t('bot', 'Tariff, price') ?>: <?= $chatMember->membership_tariff_price ?> <?= $chatMember->chat->currency->code ?><br/>
<br/>
<?= Yii::t('bot', 'Tariff, days') ?>: <?= $chatMember->membership_tariff_days ?><br/>
