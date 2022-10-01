<?php

use app\modules\bot\components\helpers\ExternalLink;

?>
<b>OpenSourceWebsite (OSW)</b> - <?= Yii::t('bot', 'online community managed by users using electronic voting and modifying source code'); ?>. <?= Yii::t('bot', 'Welcome developers, activists, volunteers, sponsors'); ?>. <?= Yii::t('bot', 'Join us and let’s build the future together'); ?>!<br/>
<br/>
<?= Yii::t('bot', 'Website') ?>: <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['invite/' . $globalUser->id]); ?>">opensourcewebsite.org</a><br/>
<?= Yii::t('bot', 'Telegram Bot'); ?>: <a href="<?= ExternalLink::getBotStartLink($user->getProviderUserId()); ?>">@opensourcewebsite_bot</a><br/>
<?= Yii::t('bot', 'Source Code') ?>: <a href="<?= ExternalLink::getGithubLink(); ?>">GitHub</a><br/>
<?= Yii::t('bot', 'Discord') ?>: <a href="<?= ExternalLink::getDiscordLink(); ?>"><?= Yii::t('bot', 'link') ?></a><br/>
<?= Yii::t('bot', 'Telegram group') ?>: <a href="<?= ExternalLink::getTelegramGroupLink(); ?>"><?= Yii::t('bot', 'link') ?></a><br/>
<?= Yii::t('bot', 'Telegram channel') ?>: <a href="<?= ExternalLink::getTelegramChannelLink(); ?>"><?= Yii::t('bot', 'link') ?></a><br/>
