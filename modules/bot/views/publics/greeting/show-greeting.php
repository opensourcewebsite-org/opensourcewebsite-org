<?php
/**
 * @var $user app\modules\bot\models\User
 * @var $chatGreetingMessage app\modules\bot\models\BotChatGreetingMessage
 */
?>
<?= Yii::t('bot', 'Welcome') ?>, <?= $user->getFullLink(); ?>!<br/>
<? if (isset($chatGreetingMessage)): ?>
<?= ($chatGreetingMessage->value); ?>
<? endif; ?>