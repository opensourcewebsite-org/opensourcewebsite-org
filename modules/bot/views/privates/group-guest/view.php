<b><?= $chat->title ?></b><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
<br/>
<?php if ($chat->description) : ?>
<br/>
<?= nl2br($chat->description); ?><br/>
<?php endif; ?>
<?php if ($chatMember->limiter_date) : ?>
<br/>
————<br/>
<br/>
<?= Yii::t('bot', 'You can send messages until') ?>: <?= $chatMember->limiter_date; ?><br/>
<?php endif; ?>
