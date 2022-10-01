<?php

use app\modules\bot\components\helpers\Emoji;

?>
<?= $isNewMatch ? Emoji::NEW1 . ' ' : '' ?><?= Emoji::AD_SEARCH ?> <b><?= Yii::t('bot', $model->getSectionName()) ?>: #<?= $model->id ?> <?= $model->title ?></b><br/>
<?php if ($keywords = $model->getKeywordsAsArray()) : ?>
<br/>
<i>#<?= implode(' #', $keywords); ?></i><br/>
<?php endif; ?>
<?php if ($model->description) : ?>
<br/>
<?= nl2br($model->description); ?><br/>
<?php endif; ?>
<?php if ($globalUser = $model->user) : ?>
————<br/>
<?php if ($user = $globalUser->botUser) : ?>
<?= Emoji::RIGHT ?> <?= $user->getFullLink(); ?><br/>
<br/>
<?php endif; ?>
<b>OSW ID</b>: #<?= $globalUser->getIdFullLink() ?><?= ($globalUser->username ? ' @' . $globalUser->username : '') ?><br/>
<b><?= Yii::t('user', 'Rank') ?></b>: <?= $globalUser->getRank() ?><br/>
<b><?= Yii::t('user', 'Real confirmations') ?></b>: <?= $globalUser->getRealConfirmations() ?><br/>
<?php endif; ?>
