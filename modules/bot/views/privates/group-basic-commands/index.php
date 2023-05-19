<b><?= $chat->title ?></b><br/>
<br/>
<?= Yii::t('bot', 'Basic Commands') ?> - <?= Yii::t('bot', 'built-in basic commands for the group') ?>.<br/>
————<br/>
<b><?= Yii::t('bot', 'For active administrators') ?>.</b><br/>
<br/>
<?= Yii::t('bot', 'As a reply to another message') ?>:<br/>
  <code>/warn</code> - <?= Yii::t('bot', 'delete a member\'s message, send a personal notification about violation of group rules') ?>.<br/>
  <code>/mute</code> - <?= Yii::t('bot', 'delete a member\'s message, send a personal notification about violation of group rules, mute the member in the group') ?>.<br/>
  <code>/ban</code> - <?= Yii::t('bot', 'delete a member\'s message, send a personal notification about violation of group rules, ban the member in the group') ?>.<br/>
————<br/>
<b><?= Yii::t('bot', 'For members') ?>.</b><br/>
<br/>
<?= Yii::t('bot', 'As a separate message') ?>:<br/>
  <code>/chat_id</code> - <?= Yii::t('bot', 'show Group ID and Topic ID') ?>.<br/>
  <code>/my_id</code> - <?= Yii::t('bot', 'show current member ID') ?>.<br/>
  <code>/my_rank</code> - <?= Yii::t('bot', 'show current member Rank') ?>.<br/>
  <code>/tip</code> - <?= Yii::t('bot', 'send financial gifts for a group') ?>.<br/>
<br/>
<?= Yii::t('bot', 'As a reply to another message') ?>:<br/>
  <code>/id</code> - <?= Yii::t('bot', 'show member information') ?>.<br/>
  <code>/tip</code> - <?= Yii::t('bot', 'send financial thanks for a member') ?>.<br/>
