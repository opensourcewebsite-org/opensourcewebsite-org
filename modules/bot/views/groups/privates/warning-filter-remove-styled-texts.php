<b><?= $chat->title ?></b><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
<br/>
<?= Yii::t('bot', 'Your last message in the group was deleted because') ?>:<br/>
<br/>
<?= Yii::t('bot', 'Styled text is present') ?>.<br/>
<br/>
<blockquote><?= nl2br($message) ?></blockquote>
