<b><?= $chat->title ?></b><br/>
<br/>
<?= Yii::t('bot', 'Administrators who can manage the channel') ?>:<br/>
<?php foreach ($administrators as $user) : ?>
  • <?= $user->getFullLink(); ?><br/>
<?php endforeach; ?>
<br/>
<?= Yii::t('bot', 'Only the owner of the channel can configure the list of administrators who have access to the settings of this channel') ?>.<br/>
