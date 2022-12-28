<b><?= $chat->title ?></b><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
<br/>
<?= Yii::t('bot', 'Group administrator deleted your last message') ?><br/>
<br/>
<?= Yii::t('bot', 'You were muted indefinitely') ?>
