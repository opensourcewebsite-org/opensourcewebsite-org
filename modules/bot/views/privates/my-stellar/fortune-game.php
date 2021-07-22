<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;

$croupier = Yii::$app->params['stellar']['croupier_public_key'] ?? null;

?>
<b><?= Yii::t('bot', 'Fortune Game') ?></b><br/>
<br/>
<?php if ($croupier) : ?>
<?= Yii::t('bot', 'Try your luck at blockchain based fortune game with transparent open source winning algorithms') ?>. <?= Yii::t('bot', 'Your prize will be instantly and automatically sent to your Stellar account') ?>.<br/>
<br/>
<?= Yii::t('bot', 'Every bet has a chance to win a prize that significantly exceeds the bet') ?>:<br/>
  • x2<br/>
  • x3<br/>
  • x4<br/>
  • x5<br/>
  • x10<br/>
  • x20<br/>
  • x50<br/>
  • x100<br/>
  • x500<br/>
  • x1 000<br/>
  • x10 000<br/>
  • x100 000<br/>
  • <?= Yii::t('bot', 'and even') ?> x1 000 000 !!!<br/>
<br/>
<?= Yii::t('bot', 'Prize Fund') ?>: 10 XLM<br/>
<br/>
<?= Yii::t('bot', 'To get started, send any amount of XLM to OSW account {0} as a bet', ExternalLink::getStellarExpertAccountFullLink($croupier)) ?>. <?= Yii::t('bot', 'Minimum bet {0} XLM', '0.001') ?>. <?= Yii::t('bot', 'Unlimited attempts') ?>.<br/>
<br/>
<?php endif; ?>
<i><?= Yii::t('bot', 'If you have any suggestions, questions or feedback, please contact our team') ?>: @opensourcewebsite</i>
