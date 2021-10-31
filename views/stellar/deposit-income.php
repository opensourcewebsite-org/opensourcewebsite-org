<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\models\StellarOperator;

/**
 * @var View $this
 */

$this->title = Yii::t('bot', 'Deposit Income');
$this->params['breadcrumbs'][] = 'Stellar';

$stellarOperator = new StellarOperator();
?>
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
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?= Yii::t('bot', 'Start earning {0} weekly deposit income every Friday with OSW stablecoins, become the community ambassador and redeem the stablecoins with other users', '0.5%') ?>.<br/>
                <br/>
                <?= Yii::t('bot', 'Stablecoins') ?>: <?= implode(', ', $assets) ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
