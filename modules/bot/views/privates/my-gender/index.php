<b><?= Yii::t('bot', 'Your Gender') ?></b><br/>
<br/>
<?php if (isset($gender)) : ?>
<?= Yii::t('bot', $gender) ?>
<?php else : ?>
<?= Yii::t('bot', 'Unknown') ?>
<?php endif; ?>
