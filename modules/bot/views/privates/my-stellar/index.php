<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;

$issuer = 'GC45AYPXRDYKK75HFWH5CUANMDFLQW34ZAXD5ZRTIAGS262XSBTFTCLH';

$assets = [
    ExternalLink::getStellarExpertAssetFullLink('EUR', $issuer, 'EUR'),
    ExternalLink::getStellarExpertAssetFullLink('USD', $issuer, 'USD'),
    ExternalLink::getStellarExpertAssetFullLink('THB', $issuer, 'THB'),
    ExternalLink::getStellarExpertAssetFullLink('RUB', $issuer, 'RUB'),
    ExternalLink::getStellarExpertAssetFullLink('UAH', $issuer, 'UAH'),
];

?>
<b><?= Yii::t('bot', 'Your Stellar Account') ?></b><br/>
<br/>
<?= Yii::t('bot', 'Public Key') ?> (<?= $stellar->isConfirmed() ? Yii::t('bot', 'confirmed') : Yii::t('bot', 'added {0}', Yii::$app->formatter->asRelativeTime($stellar->created_at)) ?>): <?= ExternalLink::getStellarExpertAccountFullLink($stellar->getPublicKey()) ?><br/>
<br/>
<?php if (!$stellar->isConfirmed()) : ?>
<?= Emoji::WARNING ?> <?= Yii::t('bot', 'Confirm your Stellar account') ?>.
<?php if (isset(Yii::$app->params['stellar']['distributor_public_key'])) : ?>
 <?= Yii::t('bot', 'In the next {0,number} minutes, send any amount of XLM to OSW account "{1}" and then click the "CONFIRM" button', [$stellar->getTimeLimit(), ExternalLink::getStellarExpertAccountFullLink(Yii::$app->params['stellar']['distributor_public_key'])]) ?>.<br/>
<?php endif; ?>
<br/>
<?php endif; ?>
————<br/>
<br/>
<?= Yii::t('bot', 'Receive {0} weekly deposit income every Friday with our stablecoins, become our community ambassador and redeem the stablecoins with other users', '0.5%') ?>.<br/>
<br/>
<?= implode(' | ', $assets) ?><br/>
