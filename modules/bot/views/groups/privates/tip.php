<b><?= $chat->title ?></b><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
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
<?php if ($contact = $globalUser->contact ?: $globalUser->newContact) : ?>
<?php if ($contact->name) : ?>
<b><?= Yii::t('user', 'Name') ?></b>: <?= $contact->name ?><br/>
<?php endif; ?>
<b><?= Yii::t('app', 'Personal identification') ?></b>: <?= $contact->getIsRealLabel() ?><br/>
<b><?= Yii::t('app', 'Personal relation') ?></b>: <?= $contact->getRelationLabel() ?><br/>
<?php endif; ?>
<?php endif; ?>
