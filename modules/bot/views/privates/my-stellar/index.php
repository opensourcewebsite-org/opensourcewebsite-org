<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;

?>
<b><?= Yii::t('bot', 'Your Stellar Account') ?></b><br/>
<br/>
<?= Yii::t('bot', 'Public Key') ?>: <?= ExternalLink::getStellarExpertAccountFullLink($stellar->getPublicKey()) ?><br/>
<br/>
<?php if (!$stellar->isConfirmed()) : ?>
  <?= Emoji::WARNING ?> <?= Yii::t('bot', 'Confirm your Stellar account') ?>. <?= Yii::t('bot', 'Public Key added') ?> <?= Yii::$app->formatter->asRelativeTime($stellar->created_at) ?>.
  <?php if (isset(Yii::$app->params['stellar']['distributor_public_key'])) : ?>
    <?= Yii::t('bot', 'In the next {0,number} minutes, send any amount of XLM to OSW account "{1}" and then click the "Confirm" button', [10, Yii::$app->params['stellar']['distributor_public_key']]) ?>. (in development)<br/>
  <?php endif; ?>
  <br/>
<?php endif; ?>
