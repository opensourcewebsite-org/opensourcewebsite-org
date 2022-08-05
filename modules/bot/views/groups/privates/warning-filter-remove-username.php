<b><?= $chat->title ?></b><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
<br/>
<?= Yii::t('bot', 'Your last message in the group was deleted due to violation of group rules') ?>:<br/>
<br/>
<?= Yii::t('bot', 'Some @username is present') ?>.
