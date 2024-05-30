<?php
/**
 * @var $user app\modules\bot\models\User
 */
?>
<?= Yii::t('bot', 'Welcome') ?>, <?= $user->getFullLink(); ?>!<br/>
<br/>
<?= Yii::t('bot', 'Before joining this group, read the group rules and press {0} to confirm that you are a human', '👍') ?>.<br/>
