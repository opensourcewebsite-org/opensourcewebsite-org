<?php

use app\modules\bot\components\helpers\Emoji;

?>
<?= Emoji::GIFT ?> <b><?= Yii::t('bot', 'Tips') ?></b>:<br/>
<br/>
<i><?= Yii::t('bot', 'You can take tip gift by click the button bellow') ?></i><br/>
<br/>
<?= Yii::t('bot', 'Gift size') ?>: <?= $chatTipQueue->userAmount; ?> <?= $chatTipQueue->currency->code; ?><br/>
<?= Yii::t('bot', 'Spots') ?>: <?= $chatTipQueue->userCount; ?>
