<?php

use app\components\helpers\TimeHelper;

?>
<b><?= Yii::t('bot', 'Group') ?>: <?= $chat->title ?></b><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
<br/>
<?= Yii::t('bot', 'Administrators who can manage the group') ?>:<br/>
<?php foreach ($administrators as $user) : ?>
  • <?= $user->getFullLink(); ?><br/>
<?php endforeach; ?>
<br/>
<i><?= Yii::t('bot', 'Only the owner of the group can configure the list of administrators who have access to the settings of this group') ?>.</i><br/>
————<br/>
<?= Yii::t('bot', 'Timezone') ?>: <?= TimeHelper::getNameByOffset($chat->timezone) ?><br/>
<?php if ($currency = $chat->currency) : ?>
<?= Yii::t('bot', 'Currency') ?>: <?= $currency->code . ' - ' . $currency->name ?><br/>
<?php endif; ?>
<br/>
<?= Yii::t('bot', 'Public link to view information about the group'); ?>: <?= $chat->getLink() ?><br/>
————<br/>
<?= Yii::t('bot', 'Available commands in this group (as a reply to another message)') ?>:<br/>
  <code>/warn</code> - <?= Yii::t('bot', 'delete a member\'s message, send a personal notification about violation of group rules') ?>.<br/>
  <code>/mute</code> - <?= Yii::t('bot', 'delete a member\'s message, send a personal notification about violation of group rules, mute the member in the group') ?>.<br/>
  <code>/ban</code> - <?= Yii::t('bot', 'delete a member\'s message, send a personal notification about violation of group rules, ban the member in the group') ?>.<br/>
