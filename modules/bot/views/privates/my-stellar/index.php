<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\models\StellarOperator;

?>
<b><?= Yii::t('bot', 'Your Stellar Account') ?> (<?= $stellar->isConfirmed() ? Yii::t('bot', 'confirmed') : Yii::t('bot', 'not confirmed') ?>)</b><br/>
<br/>
<?= Yii::t('bot', 'Public Key') ?>: <?= ExternalLink::getStellarExpertAccountFullLink($stellar->getPublicKey()) ?><br/>
<br/>
<?php if (!$stellar->isConfirmed()) : ?>
<?= Emoji::WARNING ?> <?= Yii::t('bot', 'Confirm your Stellar account') ?> (<?= Yii::t('bot', 'added {0}', Yii::$app->formatter->asRelativeTime($stellar->created_at)) ?>).<br/>
<?php if (StellarOperator::getDistributorPublicKey()) : ?>
<br/>
<?= Yii::t('bot', 'In the next {0,number} minutes, send any amount of XLM to OSW account {1} and then click the "CONFIRM" button', [$stellar->getTimeLimit(), ExternalLink::getStellarExpertAccountFullLink(StellarOperator::getDistributorPublicKey())]) ?>.<br/>
<?php endif; ?>
<br/>
<?php endif; ?>
