<?php

use app\modules\bot\components\helpers\Emoji;

?>
<b><?= $chat->title ?></b><br/>
<br/>
<?= $chatMember->user->getFullLink() ?><br/>
<?php if ($chatMember->membership_note) : ?>
<br/>
<?= Yii::t('bot', 'Note') ?>: <?= $chatMember->membership_note ?><br/>
<?php endif; ?>
<br/>
<?= Yii::t('bot', 'Premium membership is valid until') ?>: <?= $chatMember->membership_date ?><br/>
<?php if (!$currency = $chat->currency) : ?>
<br/>
<?= Emoji::WARNING ?> <?= Yii::t('bot', 'Select a currency in the group settings') ?>.<br/>
<?php else : ?>
<?php if ($chatMember->membership_tariff_price) : ?>
<br/>
<?= Yii::t('bot', 'Tariff, price') ?>: <?= $chatMember->membership_tariff_price ?> <?= $currency->code ?> (<?= $chatMember->getMembershipTariffPriceBalance() ?>)<br/>
<?php endif; ?>
<?php if ($chatMember->membership_tariff_days) : ?>
<br/>
<?= Yii::t('bot', 'Tariff, days') ?>: <?= $chatMember->membership_tariff_days ?> (<?= $chatMember->getMembershipTariffDaysBalance() ?>)<br/>
<?php endif; ?>
<?php endif; ?>
<?php if ($chatMember->limiter_date) : ?>
<br/>
<?= Yii::t('bot', 'Verification is valid until') ?>: <?= $chatMember->limiter_date ?><br/>
<?php endif; ?>
