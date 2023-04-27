<?php

use app\components\helpers\Html;

?>
<b><?= Yii::t('bot', 'Group') ?>: <?= $chat->title ?><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?></b><br/>
<?php if ($membershipTag = $chatMember->getMembershipTag()) : ?>
<br/>
<b><?= Yii::t('bot', 'Member status') ?>:</b> <?= $membershipTag ?><br/>
<?php endif; ?>
<br/>
<b><?= Yii::t('bot', 'Telegram') ?> ID</b>: #<?= $user->getIdFullLink() ?><?= ($user->provider_user_name ? ' @' . $user->provider_user_name : '') ?><br/>
<?php if ($user->provider_user_first_name) : ?>
<b><?= Yii::t('bot', 'First Name') ?></b>: <?= $user->provider_user_first_name ?><br/>
<?php endif; ?>
<?php if ($user->provider_user_last_name) : ?>
<b><?= Yii::t('bot', 'Last Name') ?></b>: <?= $user->provider_user_last_name ?><br/>
<?php endif; ?>
<?php if ($globalUser = $user->globalUser) : ?>
<br/>
<b>OSW ID</b>: #<?= $globalUser->getIdFullLink() ?><?= ($globalUser->username ? ' @' . $globalUser->username : '') ?><br/>
<b><?= Yii::t('user', 'Rank') ?></b>: <?= $globalUser->getRank() ?><br/>
<b><?= Yii::t('user', 'Real confirmations') ?></b>: <?= $globalUser->getRealConfirmations() ?><br/>
<br/>
<?php if ($contact->name) : ?>
<b><?= Yii::t('user', 'Name') ?></b>: <?= $contact->name ?><br/>
<?php endif; ?>
<b><?= Yii::t('app', 'Personal identification') ?></b>: <?= $contact->getIsRealLabel() ?><br/>
<b><?= Yii::t('app', 'Personal relation') ?></b>: <?= $contact->getRelationLabel() ?><br/>
<?php endif; ?>
<?php if ($chatMember->intro) : ?>
————<br/>
<b><?= Yii::t('bot', 'Intro') ?></b>:<br/>
<br/>
<?= nl2br($chatMember->intro) ?><br/>
<?php endif; ?>
<?php if ($review && $review->text) : ?>
————<br/>
<b><?= Yii::t('bot', 'Your public review') ?></b>:<br/>
<br/>
<?= nl2br($review->text) ?><br/>
<br/>
<?= $review->getStatusLabel() ?> - <?= $review->getStatusInfo() ?>.<br/>
<?php endif; ?>
