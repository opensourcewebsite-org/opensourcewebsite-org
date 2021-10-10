<?php

use app\modules\bot\components\helpers\ExternalLink;

?>
<?= Yii::t('bot', 'This group for') ?>: <?= $isModeSigners ? Yii::t('bot', 'Signers') : Yii::t('bot', 'Holders') ?><br/>
<?php if ($chat->stellar_asset && $chat->stellar_issuer) : ?>
<br/>
<?= Yii::t('bot', 'Asset') ?>: <?= ExternalLink::getStellarExpertAssetFullLink($chat->stellar_asset, $chat->stellar_issuer) ?><br/>
<?php endif; ?>
<?php if (!$isModeSigners && $chat->stellar_threshold) : ?>
<br/>
<?= Yii::t('bot', 'Threshold for holders') ?>: <?= $chat->stellar_threshold . ' ' . $chat->stellar_asset?><br/>
<?php endif; ?>
<?php if ($verifiedUsers) : ?>
<?php if ($isModeSigners) : ?>
<br/>
<?= Yii::t('bot', 'Verified signers') ?>:<br/>
<?php foreach ($verifiedUsers as $user) : ?>
  • <?= $user->getFullLink(); ?><br/>
<?php endforeach; ?>
<?php elseif (false) : ?>
<br/>
<?= Yii::t('bot', 'Verified holders') ?>:<br/>
<?php foreach ($verifiedUsers as $user) : ?>
  • <?= $user->getFullLink(); ?><br/>
<?php endforeach; ?>
<?php endif; ?>
<?php endif; ?>
