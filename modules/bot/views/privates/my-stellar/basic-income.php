<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\models\StellarGiver;
use app\components\helpers\Html;

$stellarGiver = new StellarGiver();
?>
<b><?= Yii::t('bot', 'Basic Income') ?></b><br/>
<br/>
<?php if ($user->isBasicIncomeOn()) : ?>
<?php if ($user->stellar->isConfirmed()) : ?>
<?php if ($user->isBasicIncomeActivated()) : ?>
<?= Emoji::STATUS_ON ?> <?= Yii::t('bot', 'You are a participant of this program and receive a weekly basic income') ?>.<br/>
<?php else : ?>
<?= Emoji::STATUS_PENDING ?> <?= Yii::t('bot', 'You are a candidate for this program and your application is pending review by other users') ?>.<br/>
<?php endif; ?>
<?php else : ?>
<?= Emoji::WARNING ?> <?= Yii::t('bot', 'Confirm your Stellar account in order for your application to be processed') ?>.<br/>
<?php endif; ?>
<?php else : ?>
<?= Emoji::STATUS_OFF ?> <?= Yii::t('bot', 'You have refused to participate in this program') ?>.<br/>
<?php endif; ?>
<?php if (StellarGiver::getGiverPublicKey()) : ?>
<br/>
————<br/>
<br/>
<?= Yii::t('bot', 'Start earning a weekly basic income every Friday') ?>. <?= Yii::t('bot', 'Weekly {0}% of the total basic income fund is sent in equal parts to all eligible participants', StellarGiver::WEEKLY_PAYMENT_PERCENT) ?>. <?= Yii::t('bot', 'The total basic income fund is formed by donations from people who support the principles and values of the free society') ?>.<br/>
<br/>
<?= Yii::t('bot', 'Weekly payment to each participant') ?>: <?= $stellarGiver->getPaymentAmount() ?> XLM. <?= Yii::t('bot', 'Total participants') ?>: <?= $stellarGiver->getParticipantsCount() ?>. <?= Yii::t('bot', 'Weekly Basic Income Fund') ?>: <?= $stellarGiver->getAvailableBalance() ?> XLM.<br/>
<br/>
<?= Yii::t('bot', 'Any person who meets these criteria can become a participant in a free society and receive a weekly basic income') ?>:<br/>
  • <?= Html::a(Yii::t('bot', 'adhere to the principles and values of a free society'), 'https://en.wikipedia.org/wiki/Non-aggression_principle') ?>.<br/>
  • <?= Html::a(Yii::t('bot', 'be a resident of Montenegro'), 'https://en.wikipedia.org/wiki/Montenegro') ?>.<br/>
  • <?= Html::a(Yii::t('bot', 'be fully capable'), 'https://en.wikipedia.org/wiki/Capacity_(law)') ?>.<br/>
<br/>
<?= Yii::t('bot', 'To support the free society and increase the weekly payments to its participants, send any amount of XLM to OSW account {0} as a donation', ExternalLink::getStellarExpertAccountFullLink(StellarGiver::getGiverPublicKey())) ?>. <?= Yii::t('bot', 'Thank you for your support') ?>!<br/>
<?php endif; ?>
