<b><?= Yii::t('bot', 'Awesome members'); ?></b><br/>
<br/>
<?php foreach ($users as $user) : ?>
<?= $user['username']; ?>: <?= $user['rating']; ?><br/>
<?php endforeach; ?>
