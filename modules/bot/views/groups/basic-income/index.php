<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\models\StellarGiver;
use app\components\helpers\Html;

$stellarGiver = new StellarGiver();
?>
<b><?= Yii::t('bot', 'Basic Income') ?></b><br/>
<?php if (StellarGiver::getGiverPublicKey()) : ?>
<br/>
<?= Yii::t('bot', 'Start earning a weekly basic income every Friday') ?>. <?= Yii::t('bot', 'Weekly {0}% of the total basic income fund is sent in equal parts to all eligible participants', StellarGiver::WEEKLY_PAYMENT_PERCENT) ?>. <?= Yii::t('bot', 'The total basic income fund is formed by donations from people who support the principles and values of the free society') ?>.<br/>
<br/>
<?= Yii::t('bot', 'Weekly payment to each participant') ?>: <b><?= $stellarGiver->getPaymentAmount() ?> XLM</b><br/>
<br/>
<?= Yii::t('bot', 'Total participants') ?>: <b><?= $stellarGiver->getParticipantsCount() ?></b><br/>
<?= Yii::t('bot', 'Weekly Basic Income Fund') ?>: <b><?= $stellarGiver->getAvailableBalance() ?> XLM</b><br/>
<br/>
<?= Yii::t('bot', 'Any person who meets these criteria can become a participant in a free society and receive a weekly basic income') ?>:<br/>
<br/>
• <?= Html::a(Yii::t('bot', 'adhere to the principles and values of a free society'), 'https://en.wikipedia.org/wiki/Non-aggression_principle') ?>.<br/>
• <?= Html::a(Yii::t('bot', 'be a resident of Montenegro'), 'https://en.wikipedia.org/wiki/Montenegro') ?>.<br/>
• <?= Html::a(Yii::t('bot', 'be fully capable'), 'https://en.wikipedia.org/wiki/Capacity_(law)') ?>.<br/>
<br/>
<?= Yii::t('bot', 'To support the free society and increase the weekly payments to its participants, send any amount of XLM to OSW account {0} as a donation', ExternalLink::getStellarExpertAccountFullLink(StellarGiver::getGiverPublicKey())) ?>. <?= Yii::t('bot', 'When we help one another, everybody wins') ?>. <?= Yii::t('bot', 'Pay it forward') ?>!<br/>
<?php endif; ?>
