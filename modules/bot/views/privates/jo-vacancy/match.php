<?php

use app\models\Resume;
use app\modules\bot\components\helpers\Emoji;

?>
<?= Emoji::JO_RESUME ?> <b><?= Yii::t('bot', 'Resume') ?>: #<?= $model->id ?> <?= $model->name ?></b><br/>
<?php if ($keywords != '') : ?>
<br/>
# <i><?= $keywords ?></i><br/>
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
<?php if ($user = $model->user->botUser) : ?>
————<br/>
<b><?= Yii::t('bot', 'Contact') ?></b>: <?= $user->getFullLink(); ?>
<?php endif; ?>
