<?php

use app\modules\bot\components\helpers\Emoji;

?>

<b><?= Yii::t('bot', 'Send @username or Telegram ID of the recipient') ?>:</b><br/>
<?php if (isset($error)): ?>
<br/><i><?=Emoji::WARNING?> <?= $error ?>.</i><br/>
<?php endif; ?>
