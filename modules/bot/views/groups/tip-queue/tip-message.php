<?php

use app\helpers\Number;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\ChatTipQueue;

?>
<?= Emoji::GIFT ?> <b><?= Yii::t('bot', 'Gifts') ?></b>:<br/>
<br/>
<?= Yii::t('bot', 'Gift') ?>: <?= $chatTipQueue->userAmount; ?> <?= $chatTipQueue->currency->code; ?><br/>
<br/>
<?php if ($chatTipQueue->state == ChatTipQueue::OPEN_STATE): ?>
<?= Yii::t('bot', 'Available') ?>: <?= $chatTipQueue->getQueueAvailableUsersCount(); ?>/<?= $chatTipQueue->userCount ?><br/>
<br/>
<i><?= Yii::t('bot', 'Any member of the group can receive a financial gift using this bot') ?>.</i><br/>
<?php else : ?>
<?= Yii::t('bot', 'Granted') ?>: <?= $chatTipQueue->userCount ?><br/>
<?php endif; ?>
