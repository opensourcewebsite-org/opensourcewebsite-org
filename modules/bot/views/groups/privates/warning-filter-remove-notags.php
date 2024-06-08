<b><?= $chat->title ?></b><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
<br/>
<?= Yii::t('bot', 'Your last message in the group was deleted because') ?>:<br/>
<br/>
<?= Yii::t('bot', 'The message was posted without any tags') ?>. <?= Yii::t('bot', 'Use any # tag in a new message to start a new discussion, or post a message as a reply to another member\'s message') ?>.<br/>
<br/>
<blockquote><?= nl2br($message) ?></blockquote>

