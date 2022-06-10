<b><?= $chat->title ?></b><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
<?php if ($chat->description) : ?>
<br/>
<?= nl2br($chat->description); ?><br/>
<?php endif; ?>
<?php if ($chatMember->membership_date || $chatMember->limiter_date) : ?>
<br/>
————<br/>
<?php if ($chatMember->membership_date) : ?>
<br/>
<?= Yii::t('bot', 'Your privileged membership is valid until') ?>: <?= $chatMember->membership_date; ?><br/>
<?php endif; ?>
<?php if ($chatMember->limiter_date) : ?>
<br/>
<?= Yii::t('bot', 'You can send messages until') ?>: <?= $chatMember->limiter_date; ?><br/>
<?php endif; ?>
<?php endif; ?>
