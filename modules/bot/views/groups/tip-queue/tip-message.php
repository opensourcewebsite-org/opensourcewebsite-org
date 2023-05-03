<?php

use app\helpers\Number;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\ChatTipQueue;

?>
<?= Emoji::GIFT ?> <b><?= Yii::t('bot', 'Gifts') ?></b>:<br/>
<br/>
<?php if ($chatTipQueue->state == ChatTipQueue::OPEN_STATE): ?>
<i><?= Yii::t('bot', 'You can take tip gift by click the button bellow') ?></i><br/>
<?php else: ?>
<i><?= Yii::t('bot', 'Gifts are completed') ?>!</i><br/>   
<?php endif; ?>
<br/>
<?= Yii::t('bot', 'Gift size') ?>: <?= $chatTipQueue->userAmount; ?> <?= $chatTipQueue->currency->code; ?><br/>
<?= Yii::t('bot', 'Spots') ?>: <?= $chatTipQueue->getQueueProcessedUsersCount(); ?>/<?= $chatTipQueue->userCount ?><br/>
<?= Yii::t('bot', 'Total paid') ?>: <?= $chatTipQueue->getQueuePaidSum(); ?> <?= $chatTipQueue->currency->code; ?><br/>
