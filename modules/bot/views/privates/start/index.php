<?php

use app\modules\bot\components\helpers\ExternalLink;

?>
<?= Yii::t('bot', 'Welcome'); ?>!<br/>
<br/>
<b>OpenSourceWebsite (OSW) - <?= Yii::t('bot', 'Creating an Open and Democratic Online Community'); ?>.</b><br/>
<br/>
<?= Yii::t('bot', 'We promote a free society where everyone can participate in decision-making through electronic voting and where open source guarantees data security'); ?>. <?= Yii::t('bot', 'We welcome everyone who shares our mission and is ready to contribute to the development of the community - developers, activists, volunteers, employees, partners, sponsors'); ?>. <?= Yii::t('bot', 'Join us and together we will create our future'); ?>!<br/>
<br/>
<?= Yii::t('bot', 'Website') ?>: <a href="<?= ExternalLink::getWebsiteLink(); ?>">opensourcewebsite.org</a><br/>
<?= Yii::t('bot', 'Telegram Bot'); ?>: <a href="<?= ExternalLink::getBotLink(); ?>">@opensourcewebsite_bot</a><br/>
<?= Yii::t('bot', 'Source Code') ?>: <a href="<?= ExternalLink::getGithubLink(); ?>">GitHub</a><br/>
<?= Yii::t('bot', 'Discord') ?>: <a href="<?= ExternalLink::getDiscordLink(); ?>"><?= Yii::t('bot', 'link') ?></a><br/>
<?= Yii::t('bot', 'Telegram group') ?>: <a href="<?= ExternalLink::getTelegramGroupLink(); ?>"><?= Yii::t('bot', 'link') ?></a><br/>
<?= Yii::t('bot', 'Telegram channel') ?>: <a href="<?= ExternalLink::getTelegramChannelLink(); ?>"><?= Yii::t('bot', 'link') ?></a><br/>
