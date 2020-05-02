<b><?= Yii::t('bot', 'Your Email') ?></b><br/>
<br/>
<?php if (isset($email)) : ?>
<?= $email ?>
<?php else : ?>
<?= Yii::t('bot', 'Unknown') ?>. <?= Yii::t('bot', 'Please, send your email') ?>.
<?php endif; ?>
