<?php

use \app\models\User;
?>
<b><?= \Yii::t('bot', 'Your Gender') ?></b>
<br/><br/>
<?php if (isset($gender)) : ?>
<?= \Yii::t('bot', User::FEMALE == $gender ? 'Female' : 'Male') ?>
<?php else : ?>
<?= \Yii::t('bot', 'Unknown') ?>
<?php endif; ?>
