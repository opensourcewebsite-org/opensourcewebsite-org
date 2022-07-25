<b><?= $chat->title ?></b><br/>
<br/>
<?= Yii::t('bot', 'Whitelist phrases') ?>.<br/>
<br/>
<?= Yii::t('bot', 'All messages that do not contain at least one of these phrases will be deleted') ?>. <?= Yii::t('bot', 'Messages from the bot and administrators will not be deleted') ?>.
