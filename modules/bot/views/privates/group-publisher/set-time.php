<?php

use app\components\helpers\TimeHelper;

?>
<b><?= Yii::t('bot', 'Send a time of day to auto publish the post in format «HH:MM»') ?> (<?= TimeHelper::getNameByOffset($chat->timezone) ?>):</b>