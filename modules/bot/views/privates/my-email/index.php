<?php

use app\modules\bot\components\helpers\Emoji;

?>
<b><?= Yii::t('bot', 'Your Email') ?> (<?= $user->isEmailConfirmed() ? Yii::t('bot', 'confirmed') : Yii::t('bot', 'not confirmed') ?>)</b><br/>
<br/>
<?= $userEmail->email ?><br/>
<br/>
<?php if (!$user->isEmailConfirmed()) : ?>
<?= Emoji::WARNING ?> <?= Yii::t('bot', 'Confirm your Email') ?>.<br/>
<br/>
<?= Yii::t('bot', 'An email with a confirmation link was sent to your email address') ?>. <?= Yii::t('bot', 'In order to complete the process, please click the confirmation link') ?>.<br/>
<br/>
<?= Yii::t('bot', 'If you do not receive a confirmation email, please check your spam folder') ?>.
<br/>
<?php endif; ?>
