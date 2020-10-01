<b><?= $chatTitle; ?></b><br/>
<br/>
<b><?= Yii::t('bot', 'Vote Ban'); ?></b> - <?= Yii::t('bot', 'allows members to vote for a specific member to be banned or kicked from the group'); ?>.<br/>
<br/>
<b><?= Yii::t('bot', 'Available commands in the group') ?>:</b><br/>
<br/>
  ▪️ <?= Yii::t('bot', 'Send a reply <code>{0}</code> to a message of any member, excludes administrators, to vote to ban or kick a member', 'voteban'); ?>.<br/>
