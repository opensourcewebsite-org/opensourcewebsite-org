<b><?= $chatTitle; ?></b><br/>
<br/>
<b><?= Yii::t('bot', 'Karma'); ?></b> - <?= Yii::t('bot', 'allows members to leave likes and dislikes for any member of the group'); ?>.<br/>
<br/>
<b><?= Yii::t('bot', 'Available commands in the group') ?>:</b><br/>
<br/>
  ▪️ <?= Yii::t('bot', 'Send a reply <code>+</code> to a message of any member, excludes bots, to leave your like for a member\'s message and increase member\'s reputation'); ?>.<br/>
  ▪️ <?= Yii::t('bot', 'Send a reply <code>-</code> to a message of any member, excludes bots, to leave your dislike for a member\'s message and decrease member\'s reputation'); ?>.<br/>
  ▪️ /top - <?= Yii::t('bot', 'Show a list of members with likes'); ?>.<br/>
