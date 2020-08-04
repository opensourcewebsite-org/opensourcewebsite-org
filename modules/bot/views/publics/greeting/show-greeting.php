<?php
/**
 * @var $user app\modules\bot\models\User
 */
?>
<?= Yii::t('bot', 'Welcome') ?>, <?= $user->getFullLink(); ?>!<br/>
<?php if ($message) : ?>
<br/>
<?= nl2br($message) ?><br/>
<?php endif; ?>
