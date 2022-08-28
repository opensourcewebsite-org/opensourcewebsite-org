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
<b><?= Yii::t('bot', 'Telegram') ?> ID</b>: #<?= $authorUser->getIdFullLink() ?><?= ($authorUser->provider_user_name ? ' @' . $authorUser->provider_user_name : '') ?><br/>
<?php if ($authorUser->provider_user_first_name) : ?>
<b><?= Yii::t('bot', 'First Name') ?></b>: <?= $authorUser->provider_user_first_name ?><br/>
<?php endif; ?>
<?php if ($authorUser->provider_user_last_name) : ?>
<b><?= Yii::t('bot', 'Last Name') ?></b>: <?= $authorUser->provider_user_last_name ?><br/>
<?php endif; ?>
<?php if ($globalAuthorUser = $authorUser->globalUser) : ?>
<br/>
<b>ID</b>: #<?= $globalAuthorUser->getIdFullLink() ?><?= ($globalAuthorUser->username ? ' @' . $globalAuthorUser->username : '') ?><br/>
<b><?= Yii::t('user', 'Rank') ?></b>: <?= $globalAuthorUser->getRank() ?><br/>
<?php endif; ?>
<?php endif; ?>
