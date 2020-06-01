🔫 <?= Yii::t('bot', '{user} has been kicked — the only way to get this user back is for admins to manualy unban in group settings', ['user' => $user]) ?>.<br/>
<br/>
<?= Yii::t('bot', 'Voters who chose to kick') ?>:<br/>
<?= $voters ?><br/>
<br/>
<?= Yii::t('bot', 'To start a vote, send a reply «<b>voteban</b>» to a message of any member of the group') ?>.
