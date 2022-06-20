<b><?= Yii::t('bot', 'Telegram') ?> ID</b>: #<?= $user->provider_user_id ?><?= ($user->provider_user_name ? ' @' . $user->provider_user_name : '') ?><br/>
<?php if ($user->provider_user_first_name) : ?>
<b><?= Yii::t('bot', 'First Name') ?></b>: <?= $user->provider_user_first_name ?><br/>
<?php endif; ?>
<?php if ($user->provider_user_last_name) : ?>
<b><?= Yii::t('bot', 'Last Name') ?></b>: <?= $user->provider_user_last_name ?><br/>
<?php endif; ?>
<?php if ($globalUser = $user->globalUser) : ?>
<br/>
<b>ID</b>: #<?= $globalUser->id ?><?= ($globalUser->username ? ' @' . $globalUser->username : '') ?><br/>
<b><?= Yii::t('user', 'Rank') ?></b>: <?= $globalUser->getRank() ?><br/>
<?php endif; ?>
————<br/>
<b><?= Yii::t('bot', 'Group') ?></b>:<br/>
<br/>
<?= $chat->title ?><?= $chat->username ? ' (@' . $chat->username . ')' : '' ?><br/>
<?php if ($review && $review->text) : ?>
————<br/>
<b><?= Yii::t('bot', 'Review') ?></b>: <?= $review->getStatusLabel() ?><br/>
<br/>
<?= nl2br($review->text) ?><br/>
————<br/>
<b><?= Yii::t('bot', 'Author') ?></b>:<br/>
<br/>
<b><?= Yii::t('bot', 'Telegram') ?> ID</b>: #<?= $authorUser->provider_user_id ?><?= ($authorUser->provider_user_name ? ' @' . $authorUser->provider_user_name : '') ?><br/>
<?php if ($authorUser->provider_user_first_name) : ?>
<b><?= Yii::t('bot', 'First Name') ?></b>: <?= $authorUser->provider_user_first_name ?><br/>
<?php endif; ?>
<?php if ($authorUser->provider_user_last_name) : ?>
<b><?= Yii::t('bot', 'Last Name') ?></b>: <?= $authorUser->provider_user_last_name ?><br/>
<?php endif; ?>
<?php if ($globalAuthorUser = $authorUser->globalUser) : ?>
<br/>
<b>ID</b>: #<?= $globalAuthorUser->id ?><?= ($globalAuthorUser->username ? ' @' . $globalAuthorUser->username : '') ?><br/>
<b><?= Yii::t('user', 'Rank') ?></b>: <?= $globalAuthorUser->getRank() ?><br/>
<?php endif; ?>
<?php endif; ?>
