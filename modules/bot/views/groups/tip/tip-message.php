<?php

use app\modules\bot\components\helpers\Emoji;

?>
<?= Emoji::GIFT ?> <b><?= Yii::t('bot', 'Tips') ?></b>:<br/>
<br/>
<?php foreach($totalAmounts as $code => $amount) : ?>
<?= $amount ?> <?= $code ?><br/>
<?php endforeach; ?>
<br/>
<i><?= Yii::t('bot', 'Any member of the group can add financial thanks to this author using this bot') ?></i>.<br/>
