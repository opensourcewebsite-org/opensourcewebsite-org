<b><?= $chat->title ?></b><br/>
<br/>
<?= $chatMember->user->getFullLink(); ?><br/>
<br/>
<?= Yii::t('bot', 'Limit of messages') ?>: <?= $chatMember->slow_mode_messages ?>/<?= $chatMember->slow_mode_messages_limit ?? $chat->slow_mode_messages_limit; ?><br/>
<?php if (!is_null($chatMember->slow_mode_messages_skip_hours)) : ?>
<br/>
<?= Yii::t('bot', 'Skip hours') ?>: <?= $chatMember->slow_mode_messages_skip_hours ?><br/>
<?php endif; ?>
