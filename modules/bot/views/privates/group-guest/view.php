<?php

use app\modules\bot\components\helpers\ExternalLink;

?>
<b><?= $chat->title ?></b><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
<?php if ($chat->description) : ?>
<br/>
<?= nl2br($chat->description); ?><br/>
<?php endif; ?>
<?php if ($chatMember) : ?>
<?php if ($chatMember->intro) : ?>
————<br/>
<b><?= Yii::t('bot', 'Your public intro') ?></b>:<br/>
<br/>
<?= nl2br($chatMember->intro) ?><br/>
<?php endif; ?>
<?php if ($chatMember->membership_date || $chatMember->limiter_date || $chat->isSlowModeOn() || ($chat->getUsername() && $user->getUsername())) : ?>
————
<?php if ($chatMember->membership_date) : ?>
<br/>
<?= Yii::t('bot', 'Your premium membership is valid until') ?>: <?= $chatMember->membership_date; ?><br/>
<?php endif; ?>
<?php if ($currency = $chat->currency) : ?>
<?php if ($chatMember->membership_tariff_price) : ?>
<br/>
<?= Yii::t('bot', 'Tariff, price') ?>: <?= $chatMember->membership_tariff_price ?> <?= $currency->code ?><br/>
<?php endif; ?>
<?php if ($chatMember->membership_tariff_days) : ?>
<br/>
<?= Yii::t('bot', 'Tariff, days') ?>: <?= $chatMember->membership_tariff_days ?><br/>
<?php endif; ?>
<?php endif; ?>
<?php if ($chatMember->limiter_date) : ?>
<br/>
<?= Yii::t('bot', 'You can send messages until') ?>: <?= $chatMember->limiter_date; ?><br/>
<?php endif; ?>
<?php if ($chat->isSlowModeOn()) : ?>
<br/>
<?= Yii::t('bot', 'Limit of messages per day') ?>: <?= $chatMember->slow_mode_messages_limit ?? $chat->slow_mode_messages_limit; ?><br/>
<?php endif; ?>
<?php if ($user->getUsername()) : ?>
<br/>
<?= Yii::t('bot', 'Your link for reviews') ?>: <?= $chatMember->getReviewsLink(); ?><br/>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
