<b><?= $chat->title ?></b><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
<br/>
<?= Yii::t('bot', 'Your last message in the group was deleted because') ?>:<br/>
<br/>
<?= Yii::t('bot', 'You have exceeded the allowed posting frequency') ?>. <?= Yii::t('bot', 'Try again later or tomorrow') ?>.
