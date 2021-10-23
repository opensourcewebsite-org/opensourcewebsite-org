<?php

use app\modules\bot\components\helpers\Emoji;

?>
<b>OpenSourceWebsite (OSW)</b> - <?= Yii::t('bot', 'online community managed by users using electronic voting and modifying source code'); ?>. <?= Yii::t('bot', 'Welcome developers, activists, volunteers, sponsors'); ?>. <?= Yii::t('bot', 'Join us and let’s build the future together'); ?>!<br/>
<br/>
<?= Yii::t('bot', 'Available commands') ?>: <?= implode(', ', $commands); ?><br/>
<br/>
<?= Yii::t('bot', 'Website') ?>: <a href="https://opensourcewebsite.org">opensourcewebsite.org</a><br/>
<?= Yii::t('bot', 'Source Code') ?>: <a href="https://github.com/opensourcewebsite-org/opensourcewebsite-org">GitHub</a><br/>
