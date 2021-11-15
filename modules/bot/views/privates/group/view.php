<b><?= $chat->title ?></b><br/>
<br/>
<?= Yii::t('bot', 'Administrators who can manage the group') ?>:<br/>
<?php foreach ($admins as $user) : ?>
  • <?= $user->getFullLink(); ?><br/>
<?php endforeach; ?>
<br/>
<?= Yii::t('bot', 'Only the owner of the group can configure the list of administrators who have access to the settings of this group') ?>.<br/>
<br/>
<?= Yii::t('bot', 'Select a feature to manage the group') ?>.<br/>
<br/>
