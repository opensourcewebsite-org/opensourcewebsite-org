<b><?= $chat->title ?></b><br/>
<br/>
<?= $chatMember->user->getFullLink(); ?><br/>
<?php if (!is_null($chatMember->slow_mode_messages_limit)) : ?>
<br/>
<?= Yii::t('bot', 'Limit of messages') ?>: <?= $chatMember->slow_mode_messages_limit ?><br/>
<?php endif; ?>
<?php if (!is_null($chatMember->slow_mode_messages_skip_days)) : ?>
<br/>
<?= Yii::t('bot', 'Skip days') ?>: <?= $chatMember->slow_mode_messages_skip_days ?><br/>
<?php endif; ?>
