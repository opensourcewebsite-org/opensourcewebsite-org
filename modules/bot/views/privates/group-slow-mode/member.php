<b><?= $chat->title ?></b><br/>
<br/>
<?= $chatMember->slow_mode_messages_limit . ' - ' . $chatMember->user->getFullLink(); ?><br/>
————<br/>
<?= Yii::t('bot', 'Send a maximum number of messages for this member per day to change the value') ?>.<br/>
