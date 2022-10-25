<?php

use app\modules\bot\components\helpers\Emoji;

?>
<b><?= $chat->title ?></b><br/>
<br/>
<?= $chatMember->membership_date . ' - ' . $chatMember->user->getFullLink(); ?><br/>
<?php if ($chatMember->membership_note) : ?>
<br/>
<?= Yii::t('bot', 'Note') ?>: <?= $chatMember->membership_note ?><br/>
<?php endif; ?>
<?php if (!$currency = $chat->currency) : ?>
<br/>
<?= Emoji::WARNING ?> <?= Yii::t('bot', 'Select a currency in the group settings') ?>.<br/>
<?php else : ?>
<?php if ($chatMember->membership_tariff_price) : ?>
<br/>
<?= Yii::t('bot', 'Tariff, price') ?>: <?= $chatMember->membership_tariff_price ?> <?= $currency->code ?> (<?= $chatMember->getMembershipTariffBalance() ?>)<br/>
<?php endif; ?>
<?php if ($chatMember->membership_tariff_days) : ?>
<br/>
<?= Yii::t('bot', 'Tariff, days') ?>: <?= $chatMember->membership_tariff_days ?><br/>
<?php endif; ?>
<?php endif; ?>
————<br/>
<?= Yii::t('bot', 'Send any date in format «YYYY-MM-DD» to change the date') ?>.<br/>
