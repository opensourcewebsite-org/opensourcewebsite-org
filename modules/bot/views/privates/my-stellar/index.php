<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;

$distributor = Yii::$app->params['stellar']['distributor_public_key'] ?? null;

?>
<b><?= Yii::t('bot', 'Your Stellar Account') ?></b><br/>
<br/>
<?= Yii::t('bot', 'Public Key') ?> (<?= $stellar->isConfirmed() ? Yii::t('bot', 'confirmed') : Yii::t('bot', 'added {0}', Yii::$app->formatter->asRelativeTime($stellar->created_at)) ?>): <?= ExternalLink::getStellarExpertAccountFullLink($stellar->getPublicKey()) ?><br/>
<br/>
<?php if (!$stellar->isConfirmed()) : ?>
<?= Emoji::WARNING ?> <?= Yii::t('bot', 'Confirm your Stellar account') ?>.
<?php if ($distributor) : ?>
 <?= Yii::t('bot', 'In the next {0,number} minutes, send any amount of XLM to OSW account {1} and then click the "CONFIRM" button', [$stellar->getTimeLimit(), ExternalLink::getStellarExpertAccountFullLink($distributor)]) ?>.<br/>
<?php endif; ?>
<br/>
<?php endif; ?>
