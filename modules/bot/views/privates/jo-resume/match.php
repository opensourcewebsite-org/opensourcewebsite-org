<?php

use app\models\Vacancy;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;

?>
<?= $isNewMatch ? Emoji::NEW1 . ' ' : '' ?><?= Emoji::JO_VACANCY ?> <b><?= Yii::t('bot', 'Vacancy') ?>: #<?= $model->id ?> <?= $model->name ?></b><br/>
<?php if ($company) : ?>
————<br/>
<?= Emoji::JO_COMPANY ?> <b><?= $company->name; ?></b><br/>
<?php if ($company->description) : ?>
<br/>
<?= nl2br($company->description); ?><br/>
<?php endif; ?>
<?php if ($company->address) : ?>
<br/>
<b><?= Yii::t('bot', 'Address') ?></b>: <?= $company->address ?><br/>
<?php endif; ?>
<?php if ($company->url) : ?>
<br/>
<b><?= Yii::t('bot', 'Website') ?></b>: <?= $company->url ?><br/>
<?php endif; ?>
————
<?php endif; ?>
<?php if ($keywords = $model->getKeywordsAsArray()) : ?>
<br/>
<i>#<?= implode(' #', $keywords); ?></i><br/>
<?php endif; ?>
<?php if ($model->responsibilities) : ?>
<br/>
<b><?= Yii::t('bot', 'Responsibilities') ?></b>:<br/>
<br/>
<?= nl2br($model->responsibilities) ?><br/>
<?php endif; ?>
<?php if ($model->requirements) : ?>
<br/>
<b><?= Yii::t('bot', 'Requirements') ?></b>:<br/>
<br/>
<?= nl2br($model->requirements) ?><br/>
<?php endif; ?>
<?php if ($model->conditions) : ?>
<br/>
<b><?= Yii::t('bot', 'Conditions') ?></b>:<br/>
<br/>
<?= nl2br($model->conditions) ?><br/>
<?php endif; ?>
<?php if ($model->languages) : ?>
<br/>
<b><?= Yii::t('jo', 'Required languages') ?></b>:<br/>
<br/>
<?php foreach ($model->languages as $vacancyLanguage) : ?>
  • <?= $vacancyLanguage->getLabel() ?><br/>
<?php endforeach; ?>
<?php endif; ?>
<?php if ($model->max_hourly_rate) : ?>
<br/>
<b><?= Yii::t('bot', 'Max. hourly rate') ?></b>: <?= $model->max_hourly_rate ?> <?= $model->currency->code ?><br/>
<?php endif; ?>
<br/>
<b><?= Yii::t('jo', 'Remote work') ?></b>: <?= $model->remote_on == Vacancy::REMOTE_ON ? Yii::t('bot', 'Yes') : Yii::t('bot', 'No') ; ?><br/>
<br/>
<?php if ($model->location_lat && $model->location_lon) : ?>
<b><?= Yii::t('jo', 'Offline work') ?></b>: <?= Yii::t('bot', 'Yes') ?><br/>
  • <b><?= Yii::t('bot', 'Location') ?></b>: <?= ExternalLink::getOSMFullLink($model->location_lat, $model->location_lon) ?><br/>
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
