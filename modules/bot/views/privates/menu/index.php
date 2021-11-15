<b>ID</b>: #<?= $user->id; ?><?= ($user->username ? ' @' . $user->username : '') ?><br/>
<br/>
<b><?= Yii::t('user', 'Rank') ?></b>: <?= $user->getRank(); ?><br/>
<b><?= Yii::t('user', 'Rating') ?>:</b> <?= $user->getRating() ?><br/>
<b><?= Yii::t('user', 'Real confirmations') ?></b>: <?= $user->getRealConfirmations(); ?><br/>
