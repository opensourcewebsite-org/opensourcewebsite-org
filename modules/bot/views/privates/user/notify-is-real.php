<b><?= Yii::t('bot', 'Telegram') ?> ID</b>: #<?= $authorUser->getIdFullLink() ?><?= ($authorUser->provider_user_name ? ' @' . $authorUser->provider_user_name : '') ?><br/>
<?php if ($authorUser->provider_user_first_name) : ?>
<b><?= Yii::t('bot', 'First Name') ?></b>: <?= $authorUser->provider_user_first_name ?><br/>
<?php endif; ?>
<?php if ($authorUser->provider_user_last_name) : ?>
<b><?= Yii::t('bot', 'Last Name') ?></b>: <?= $authorUser->provider_user_last_name ?><br/>
<?php endif; ?>
<?php if ($globalAuthorUser = $authorUser->globalUser) : ?>
<br/>
<b>OSW ID</b>: #<?= $globalAuthorUser->getIdFullLink() ?><?= ($globalAuthorUser->username ? ' @' . $globalAuthorUser->username : '') ?><br/>
<b><?= Yii::t('user', 'Rank') ?></b>: <?= $globalAuthorUser->getRank() ?><br/>
<b><?= Yii::t('user', 'Real confirmations') ?></b>: <?= $globalAuthorUser->getRealConfirmations() ?><br/>
<?php endif; ?>
————<br/>
<?= Yii::t('bot', 'The user has made the folowing changes to your account') ?>:<br/>
<br/>
<b><?= Yii::t('app', 'Personal identification') ?></b>: <?= $contact->getIsRealLabel() ?><br/>
