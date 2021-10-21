<?php

use app\modules\bot\components\helpers\ExternalLink;

?>
<b><?= $chat->title ?> (<?= Yii::t('bot', 'in development') ?>)</b><br/>
<br/>
<?= Yii::t('bot', 'Marketplace') ?> - <?= Yii::t('bot', 'allows members to post ads to the channel') ?>.<br/>
<br/>
<?= Yii::t('bot', 'Invite members to use this feature via the link'); ?>: <?= ExternalLink::getBotGroupGuestLink($chat->getChatId()); ?>
