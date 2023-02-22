<b><?= $chat->title ?></b><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
<br/>
<?= Yii::t('bot', 'The administrators removed your message for violating the group rules') ?>.
<br/>
<?= Yii::t('bot', 'The next violation may result in a mute or ban in the group') ?>.
