<?php

use app\components\helpers\TimeHelper;

?>
<b><?= $chat->title ?></b><br/>
<br/>
<b><?= Yii::t('bot', 'Your posts') ?>.</b><br/>
<br/>
<?= Yii::t('bot', 'Timezone') ?>: <?= TimeHelper::getNameByOffset($chat->timezone) ?><br/>
