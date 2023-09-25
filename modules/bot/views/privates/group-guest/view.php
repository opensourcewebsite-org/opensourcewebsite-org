<?php

use app\modules\bot\components\helpers\ExternalLink;

?>
<b><?= Yii::t('bot', 'Group') ?>: <?= $chat->title ?></b><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
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
<?= Yii::t('bot', 'Your premium membership is valid until') ?>: <?= $chatMember->membership_date ?><br/>
<?php endif; ?>
<?php if ($currency = $chat->currency) : ?>
<?php if ($chatMember->membership_tariff_price) : ?>
  • <?= Yii::t('bot', 'Tariff, price') ?>: <?= $chatMember->membership_tariff_price ?> <?= $currency->code ?><br/>
<?php endif; ?>
<?php if ($chatMember->membership_tariff_days) : ?>
  • <?= Yii::t('bot', 'Tariff, days') ?>: <?= $chatMember->membership_tariff_days ?><br/>
<?php endif; ?>
<?php endif; ?>
<?php if ($chatMember->limiter_date) : ?>
<br/>
<?= Yii::t('bot', 'Your verification is valid until') ?>: <?= $chatMember->limiter_date ?><br/>
<?php endif; ?>
<?php if ($chat->isSlowModeOn()) : ?>
<br/>
<?= Yii::t('bot', 'Limit of messages') ?> (<?= Yii::t('bot', 'today') ?>): <?= $chatMember->getActualSlowModeMessages() ?>/<?= $chatMember->getActualSlowModeMessagesLimit() ?><br/>
<?php endif; ?>
<?php if ($user->getUsername()) : ?>
<br/>
<?= Yii::t('bot', 'Public personal link for reviews from other group members') ?>: <?= $chatMember->getReviewsLink() ?><br/>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
<?php if ($chat->isInviterOn()) : ?>
<br/>
<?= Yii::t('bot', 'Reward amount for adding a new member to the group') ?>: <?= $chat->getDisplayRewardAmount() ?><br/>
<?php endif; ?>
