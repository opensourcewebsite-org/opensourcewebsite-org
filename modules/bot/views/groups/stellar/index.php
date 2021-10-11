<?php

use app\modules\bot\components\helpers\ExternalLink;

$assetLink = ExternalLink::getStellarExpertAssetFullLink($chat->stellar_asset, $chat->stellar_issuer, $chat->stellar_asset);
?>
<?php if ($chat->stellar_asset && $chat->stellar_issuer) : ?>
<?= $isModeSigners ? Yii::t('bot', 'This group is for {0} signers', $assetLink) : Yii::t('bot', 'This group is for {0} holders', $assetLink); ?>.<br/>
<?php endif; ?>
<?php if (!$isModeSigners && $chat->stellar_threshold) : ?>
<br/>
<?= Yii::t('bot', 'Threshold for holders') ?>: <?= $chat->stellar_threshold . ' ' . $chat->stellar_asset?><br/>
<?php endif; ?>
<?php if ($verifiedUsers) : ?>
<br/>
<?php if ($isModeSigners) : ?>
<?= Yii::t('bot', 'Verified signers') ?>:<br/>
<?php else : ?>
<?= Yii::t('bot', 'Verified holders') ?>:<br/>
<?php endif; ?>
<?php foreach ($verifiedUsers as $user) : ?>
  • <?= $user->getFullLink(); ?><br/>
<?php endforeach; ?>
<?php endif; ?>
