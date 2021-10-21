<b><?= $chat->title ?></b><br/>
<br/>
<?= Yii::t('bot', 'Administrators who can manage the channel') ?>:<br/>
<?php foreach ($admins as $user) : ?>
  • <?= $user->getFullLink(); ?><br/>
<?php endforeach; ?>
<br/>
<?= Yii::t('bot', 'Select a feature to manage the channel') ?>.<br/>
<br/>
