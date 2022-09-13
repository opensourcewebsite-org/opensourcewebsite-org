<?php

use app\components\helpers\TimeHelper;

?>
<b><?= Yii::t('bot', 'Send a time of day to publish the post in format «HH:MM»') ?> (<?= TimeHelper::getNameByOffset($chat->timezone) ?>):</b><br/>
<br/>
<i><?= Yii::t('bot', 'Only you see this information') ?>.</i>
