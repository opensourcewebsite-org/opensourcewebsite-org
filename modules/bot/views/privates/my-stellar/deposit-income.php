<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;

$issuer = Yii::$app->params['stellar']['issuer_public_key'] ?? null;

$assets = [
    ExternalLink::getStellarExpertAssetFullLink('EUR', $issuer, 'EUR'),
    ExternalLink::getStellarExpertAssetFullLink('USD', $issuer, 'USD'),
    ExternalLink::getStellarExpertAssetFullLink('THB', $issuer, 'THB'),
    ExternalLink::getStellarExpertAssetFullLink('RUB', $issuer, 'RUB'),
    ExternalLink::getStellarExpertAssetFullLink('UAH', $issuer, 'UAH'),
];

?>
<b><?= Yii::t('bot', 'Deposit Income') ?></b><br/>
<br/>
<?= Yii::t('bot', 'Start earning {0} weekly deposit income every Friday with OSW stablecoins, become the community ambassador and redeem the stablecoins with other users', '0.5%') ?>.<br/>
<br/>
<?= implode(' | ', $assets) ?><br/>
<br/>
<i><?= Yii::t('bot', 'If you have any suggestions, questions or feedback, please contact our team') ?>: @opensourcewebsite</i>
