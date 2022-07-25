<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\models\StellarOperator;

$stellarOperator = new StellarOperator();
?>
<b><?= Yii::t('bot', 'Deposit Income') ?></b><br/>
<br/>
<?php if (StellarOperator::getIssuerPublicKey()) : ?>
<?php
$assets = [
    ExternalLink::getStellarExpertAssetFullLink('EUR', StellarOperator::getIssuerPublicKey(), 'EUR'),
    ExternalLink::getStellarExpertAssetFullLink('USD', StellarOperator::getIssuerPublicKey(), 'USD'),
    ExternalLink::getStellarExpertAssetFullLink('THB', StellarOperator::getIssuerPublicKey(), 'THB'),
    ExternalLink::getStellarExpertAssetFullLink('RUB', StellarOperator::getIssuerPublicKey(), 'RUB'),
    ExternalLink::getStellarExpertAssetFullLink('UAH', StellarOperator::getIssuerPublicKey(), 'UAH'),
];
?>
<?= Yii::t('bot', 'Start earning {0}% weekly deposit income every Friday with OSW stablecoins, become the community ambassador and redeem the stablecoins with other users', StellarOperator::INTEREST_RATE_WEEKLY*100) ?>.<br/>
<br/>
<?= Yii::t('bot', 'Stablecoins') ?>: <?= implode(', ', $assets) ?><br/>
<?php endif; ?>
