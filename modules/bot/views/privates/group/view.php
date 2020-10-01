<b><?= $chatTitle ?></b><br/>
<br/>
<?= Yii::t('bot', 'Select a feature to manage the group') ?>.<br/>
<br/>
<?= Yii::t('bot', 'Administrators who can manage the group') ?>:<br/>
<?php foreach ($admins as $user) : ?>
  ▪️ <?= $user->getFullLink(); ?><br/>
<?php endforeach; ?>
