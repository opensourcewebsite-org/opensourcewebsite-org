<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\models\StellarCroupier;

$stellarCroupier = new StellarCroupier();
?>
<b><?= Yii::t('bot', 'Fortune Game') ?></b><br/>
<br/>
<?php if (StellarCroupier::getCroupierPublicKey()) : ?>
<?= Yii::t('bot', 'Try your luck at blockchain based fortune game with transparent open source winning algorithms') ?>. <?= Yii::t('bot', 'When you win, your prize will be instantly sent to your Stellar account') ?>.<br/>
<br/>
<?= Yii::t('bot', 'Prize Fund') ?>: <b><?= $stellarCroupier->getAvailableBalance() ?> XLM</b><br/>
<br/>
<?= Yii::t('bot', 'Every bet has a chance to win a prize that significantly exceeds the bet') ?>:<br/>
<br/>
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
<?= Yii::t('bot', 'To start playing, send any amount of XLM to OSW account {0} as a bet', ExternalLink::getStellarExpertAccountFullLink(StellarCroupier::getCroupierPublicKey())) ?>. <?= Yii::t('bot', 'Minimum bet is {0} XLM', StellarCroupier::BET_MINIMUM_AMOUNT) ?>. <?= Yii::t('bot', 'Unlimited attempts to win') ?>. <?= Yii::t('bot', 'Good luck') ?>!<br/>
<?php endif; ?>
