<?php

use app\models\Vacancy;
use app\modules\bot\components\helpers\Emoji;

?>
<b><?= Emoji::JOB_VACANCY . ' ' . Yii::t('bot', 'Vacancy') ?>: <?= $name ?></b><br/>
<br/>
<?php if ($keywords != '') : ?>
# <i><?= $keywords ?></i><br/>
<br/>
<?php endif; ?>
<?php if ($responsibilities) : ?>
<b><?= Yii::t('bot', 'Responsibilities') ?>:</b><br/>
<br/>
<?= nl2br($responsibilities) ?><br/>
<br/>
<?php endif; ?>
<?php if ($requirements) : ?>
<b><?= Yii::t('bot', 'Requirements') ?>:</b><br/>
<br/>
<?= nl2br($requirements) ?><br/>
<br/>
<?php endif; ?>
<?php if ($conditions) : ?>
<b><?= Yii::t('bot', 'Conditions') ?>:</b><br/>
<br/>
<?= nl2br($conditions) ?><br/>
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
<?php if ($hourlyRate) : ?>
<b><?= Yii::t('bot', 'Max. hourly rate') ?>:</b> <?= $hourlyRate ?> <?= $currencyCode ?><br/>
<br/>
<?php endif; ?>
<b><?= Yii::t('bot', 'Remote work') ?>:</b> <?= $remote_on == Vacancy::REMOTE_ON ? Yii::t('bot', 'Yes') : Yii::t('bot', 'No') ; ?><br/>
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
<?php if ($user->provider_user_name) : ?>
<b><?= Yii::t('bot', 'Contact') ?>:</b> @<?= $user->provider_user_name ?>
<?php else : ?>
<b><?= Yii::t('bot', 'Contact') ?>:</b> <a href="tg://user?id=<?= $user->provider_user_id ?>"><?= $user->provider_user_first_name ?> <?= $user->provider_user_last_name ?></a>
<?php endif; ?>
<?php endif; ?>
