<?php
/**
 * @var $user app\modules\bot\models\User
 */
?>
<?= Yii::t('bot', 'Welcome') ?>, <?= $user->getFullLink(); ?>!<br/>
<br/>
<?= Yii::t('bot', 'Before sending any messages, press {0} to verify that you are a human', '👍') ?>. <?= Yii::t('bot', 'If you don\'t solve the captcha in {0,number} mins, you will be automatically kicked out of the group', 5) ?>.<br/>
