<?php
    use \app\models\User;
?>

<b><?= \Yii::t('bot', 'Your Gender') ?></b>
<br/><br/>
<? if (isset($gender)) : ?>
    <?= \Yii::t('bot', User::FEMALE == $gender ? 'Female' : 'Male') ?>
<? else : ?>
    <?= \Yii::t('bot', 'Unknown') ?>
<? endif; ?>
