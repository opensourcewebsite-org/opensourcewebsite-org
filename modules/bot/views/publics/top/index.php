<b><?= Yii::t('bot', 'Awesome members'); ?></b><br/>
<br/>
<?php foreach ($users as $user) : ?>
<?= $user['username']; ?>: <?= $user['rating']; ?><br/>
<?php endforeach; ?>
<br/>
<?= Yii::t('bot', 'To start a vote, send a reply «<b>+</b>» or «<b>-</b>» to a message of any member of the group'); ?>.
