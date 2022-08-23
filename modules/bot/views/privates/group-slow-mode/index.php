<b><?= $chat->title ?></b><br/>
<br/>
<?= Yii::t('bot', 'Slow Mode') ?> - <?= Yii::t('bot', 'deletes messages by members that exceed the allowed posting frequency') ?>. <?= Yii::t('bot', 'Ignores bots') ?>.<br/>
<?php if ($chat->slow_mode_messages_limit) : ?>
<br/>
<?= Yii::t('bot', 'Limit of messages per member per day') ?>: <?= $chat->slow_mode_messages_limit ?><br/>
<?php endif; ?>
