<?php

use app\models\Resume;
use app\modules\bot\components\helpers\Emoji;

?>
<?= $isNewMatch ? Emoji::NEW1 . ' ' : '' ?><?= Emoji::JO_RESUME ?> <b><?= Yii::t('bot', 'Resume') ?>: #<?= $model->id ?> <?= $model->name ?></b><br/>
<?php if ($keywords = $model->getKeywordsAsArray()) : ?>
<br/>
<i>#<?= implode(' #', $keywords); ?></i><br/>
<?php endif; ?>
<?php if ($model->skills) : ?>
<br/>
<b><?= Yii::t('bot', 'Skills') ?></b>:<br/>
<br/>
<?= nl2br($model->skills) ?><br/>
<?php endif; ?>
<?php if ($model->experiences) : ?>
<br/>
<b><?= Yii::t('bot', 'Experiences') ?></b>:<br/>
<br/>
<?= nl2br($model->experiences) ?><br/>
<?php endif; ?>
<?php if ($model->expectations) : ?>
<br/>
<b><?= Yii::t('bot', 'Expectations') ?></b>:<br/>
<br/>
<?= nl2br($model->expectations) ?><br/>
<?php endif; ?>
<?php if ($model->min_hourly_rate) : ?>
<br/>
<b><?= Yii::t('bot', 'Min. hourly rate') ?></b>: <?= $model->min_hourly_rate ?> <?= $model->currency->code ?><br/>
<?php endif; ?>
<br/>
<b><?= Yii::t('jo', 'Remote work') ?></b>: <?= $model->remote_on == Resume::REMOTE_ON ? Yii::t('bot', 'Yes') : Yii::t('bot', 'No') ; ?><br/>
<br/>
<?php if ($model->location_lat && $model->location_lon) : ?>
<b><?= Yii::t('jo', 'Offline work') ?></b>: <?= Yii::t('bot', 'Yes') ?><br/>
<?php else : ?>
<b><?= Yii::t('jo', 'Offline work') ?></b>: <?= Yii::t('bot', 'No') ?><br/>
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
