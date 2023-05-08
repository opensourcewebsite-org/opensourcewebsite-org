<b><?= $chat->title ?></b><br/>
<br/>
<?= Yii::t('bot', 'Message Filter') ?> - <?= Yii::t('bot', 'filters messages in the group using a list of allowed phrases (Whitelist) or a list of forbidden phrases (Blacklist)') ?>.<br/>
<br/>
<?= Yii::t('bot', 'All messages that do not meet the requirements will be deleted') ?>. <?= Yii::t('bot', 'Ignores the bot, administrators and premium members') ?>.
