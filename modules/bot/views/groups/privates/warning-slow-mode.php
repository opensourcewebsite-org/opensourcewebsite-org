<b><?= $chat->title ?></b><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
<br/>
<?= Yii::t('bot', 'Your last message in the group was deleted because') ?>:<br/>
<br/>
<?= Yii::t('bot', 'You have reached the message limit for today') ?>. <?= Yii::t('bot', 'Try again tomorrow') ?>.
