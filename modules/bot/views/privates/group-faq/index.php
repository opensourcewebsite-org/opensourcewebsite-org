<?php

use app\modules\bot\components\helpers\ExternalLink;

?>
<b><?= $chat->title ?></b><br/>
<br/>
<?= Yii::t('bot', 'FAQ') ?> - <?= Yii::t('bot', 'answers questions and other messages from members in the group') ?>. <?= Yii::t('bot', 'Ignores bots') ?>.<br/>
<br/>
<?= Yii::t('bot', 'Invite members to use this feature via the link'); ?>: <?= ExternalLink::getBotGroupGuestLink($chat->getChatId()); ?><br/>
<br/>
<?= Yii::t('bot', 'The bot will send this link to the group as a response to the message «faq»'); ?>.
