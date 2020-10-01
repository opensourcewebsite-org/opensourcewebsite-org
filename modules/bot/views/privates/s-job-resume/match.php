<?php

use app\models\Vacancy;
use app\modules\bot\components\helpers\Emoji;

?>
<b><?= Emoji::JOB_VACANCY . ' ' . Yii::t('bot', 'Vacancy') ?>: <?= $model->name ?></b><br/>
<br/>
<?php if ($keywords != '') : ?>
# <i><?= $keywords ?></i><br/>
<br/>
<?php endif; ?>
<?php if ($model->responsibilities) : ?>
<b><?= Yii::t('bot', 'Responsibilities') ?>:</b><br/>
<br/>
<?= nl2br($model->responsibilities) ?><br/>
<br/>
<?php endif; ?>
<?php if ($model->requirements) : ?>
<b><?= Yii::t('bot', 'Requirements') ?>:</b><br/>
<br/>
<?= nl2br($model->requirements) ?><br/>
<br/>
<?php endif; ?>
<?php if ($model->conditions) : ?>
<b><?= Yii::t('bot', 'Conditions') ?>:</b><br/>
<br/>
<?= nl2br($model->conditions) ?><br/>
<br/>
<?php endif; ?>
<?php if ($languages) : ?>
<b><?= Yii::t('bot', 'Required languages') ?>:</b><br/>
<br/>
<?php foreach ($languages as $language) : ?>
<?= $language ?><br/>
<?php endforeach; ?>
<br/>
<?php endif; ?>
<?php if ($model->max_hourly_rate) : ?>
<b><?= Yii::t('bot', 'Max. hourly rate') ?>:</b> <?= $model->max_hourly_rate ?> <?= $model->currency->code ?><br/>
<br/>
<?php endif; ?>
<b><?= Yii::t('bot', 'Remote work') ?>:</b> <?= $model->remote_on == Vacancy::REMOTE_ON ? Yii::t('bot', 'Yes') : Yii::t('bot', 'No') ; ?><br/>
<br/>
<?php if ($model->location_lat && $model->location_lon) : ?>
<b><?= Yii::t('bot', 'Location') ?>:</b> <a href = "<?= $locationLink ?>"><?= $model->location_lat ?> <?= $model->location_lon ?></a><br/>
<br/>
<?php endif; ?>
<?php if ($company) : ?>
————<br/>
<br/>
<b><?= Emoji::JOB_COMPANY . ' ' . Yii::t('bot', 'Company') ?>: <?= $company->name; ?></b><br/>
<br/>
<?php if ($company->description) : ?>
<?= nl2br($company->description); ?><br/>
<br/>
<?php endif; ?>
<?php if ($company->address) : ?>
<b><?= Yii::t('bot', 'Address') ?>:</b> <?= $company->address ?><br/>
<br/>
<?php endif; ?>
<?php if ($company->url) : ?>
<b><?= Yii::t('bot', 'Website') ?>:</b> <?= $company->url ?><br/>
<br/>
<?php endif; ?>
<?php endif; ?>
<?php if ($user) : ?>
————<br/>
<br/>
<b><?= Yii::t('bot', 'Contact') ?>:</b> <?= $user->getFullLink(); ?>
<?php endif; ?>
