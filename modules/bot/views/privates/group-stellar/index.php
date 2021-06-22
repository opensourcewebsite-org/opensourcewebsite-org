<?php

use app\modules\bot\components\helpers\ExternalLink;

?>
<b><?= $chat->title ?></b><br/>
<br/>
Stellar - <?= Yii::t('bot', 'helps to manage the list of group members of holders and signers on the Stellar network, shows an invite link to this group for holders and signers who match the conditions') ?>.<br/>
<?php if ($chat->stellar_asset && $chat->stellar_issuer) : ?>
<br/>
<?= Yii::t('bot', 'Asset') ?>: <?= ExternalLink::getStellarExpertAssetFullLink($chat->stellar_asset, $chat->stellar_issuer) ?><br/>
<?php endif; ?>
<?php if (!$isModeSigners && $chat->stellar_threshold) : ?>
<br/>
<?= Yii::t('bot', 'Threshold for holders') ?>: <?= $chat->stellar_threshold ?><br/>
<?php endif; ?>
<?php if ($chat->stellar_invite_link) : ?>
<br/>
<?= Yii::t('bot', 'Invite link') ?>: <?= $chat->stellar_invite_link ?><br/>
<?php endif; ?>
————<br/>
<a href="https://www.stellar.org">Stellar.org</a>
