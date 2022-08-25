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
<?php if ($chatMember->membership_date || $chatMember->limiter_date || ($chat->getUsername() && $user->getUsername())) : ?>
————<br/>
<?php if ($chatMember->membership_date) : ?>
<?= Yii::t('bot', 'Your premium membership is valid until') ?>: <?= $chatMember->membership_date; ?><br/>
<br/>
<?php endif; ?>
<?php if ($chatMember->limiter_date) : ?>
<?= Yii::t('bot', 'You can send messages until') ?>: <?= $chatMember->limiter_date; ?><br/>
<br/>
<?php endif; ?>
<?php if ($user->getUsername()) : ?>
<?= Yii::t('bot', 'Your link for reviews') ?>: <?= $chatMember->getReviewsLink(); ?><br/>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
