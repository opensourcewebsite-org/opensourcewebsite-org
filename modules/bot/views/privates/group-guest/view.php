<?php

use app\modules\bot\components\helpers\ExternalLink;

?>
<b><?= $chat->title ?></b><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
<?php if ($chat->description) : ?>
<br/>
<?= nl2br($chat->description); ?><br/>
<?php endif; ?>
<?php if ($chatMember) : ?>
<?php if ($chatMember->membership_date || $chatMember->limiter_date || ($chat->getUsername() && $user->getUsername())) : ?>
————<br/>
<?php if ($chatMember->membership_date) : ?>
<?= Yii::t('bot', 'Your privileged membership is valid until') ?>: <?= $chatMember->membership_date; ?><br/>
<br/>
<?php endif; ?>
<?php if ($chatMember->limiter_date) : ?>
<?= Yii::t('bot', 'You can send messages until') ?>: <?= $chatMember->limiter_date; ?><br/>
<br/>
<?php endif; ?>
<?php if ($chat->getUsername() && $user->getUsername()) : ?>
<?= Yii::t('bot', 'Your link for reviews') ?>: <?= ExternalLink::getBotStartLink($user->getUsername() . '-' . $chat->getUsername()); ?><br/>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
