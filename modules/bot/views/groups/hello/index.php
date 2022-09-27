<?php

use app\modules\bot\components\helpers\ExternalLink;

?>
<b>OpenSourceWebsite (OSW)</b> - <?= Yii::t('bot', 'online community managed by users using electronic voting and modifying source code'); ?>. <?= Yii::t('bot', 'Welcome developers, activists, volunteers, sponsors'); ?>. <?= Yii::t('bot', 'Join us and let’s build the future together'); ?>!<br/>
<?php if ($commands) : ?>
<br/>
<?= Yii::t('bot', 'Available commands') ?>: <?= implode(', ', $commands); ?><br/>
<?php endif; ?>
<br/>
<?= Yii::t('bot', 'Website') ?>: <a href="<?= ExternalLink::getWebsiteLink(); ?>">opensourcewebsite.org</a><br/>
<?= Yii::t('bot', 'Source Code') ?>: <a href="<?= ExternalLink::getGithubLink(); ?>">GitHub</a><br/>
<?= Yii::t('bot', 'Discord') ?>: <a href="<?= ExternalLink::getDiscordLink(); ?>"><?= Yii::t('bot', 'link') ?></a><br/>
