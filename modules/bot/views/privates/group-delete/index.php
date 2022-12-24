<?php

use app\modules\bot\components\helpers\Emoji;

?>
<b><?= $chat->title ?></b><br/>
<br/>
<?= Emoji::WARNING ?> <?= Yii::t('bot', 'Irreversible operation') ?>! <?= Yii::t('bot', 'Delete the group and all settings?') ?><br/>
