<?php

use app\models\Resume;
use app\modules\bot\components\helpers\Emoji;

?>
<b><?= Emoji::JOB_RESUME . ' ' . Yii::t('bot', 'Resume') ?>: <?= $model->name ?></b><br/>
<br/>
<?php if ($keywords != '') : ?>
# <i><?= $keywords ?></i><br/>
<br/>
<?php endif; ?>
<?php if ($model->skills) : ?>
<b><?= Yii::t('bot', 'Skills') ?>:</b><br/>
<br/>
<?= nl2br($model->skills) ?><br/>
<br/>
<?php endif; ?>
<?php if ($model->experiences) : ?>
<b><?= Yii::t('bot', 'Experiences') ?>:</b><br/>
<br/>
<?= nl2br($model->experiences) ?><br/>
<br/>
<?php endif; ?>
<?php if ($model->expectations) : ?>
<b><?= Yii::t('bot', 'Expectations') ?>:</b><br/>
<br/>
<?= nl2br($model->expectations) ?><br/>
<br/>
<?php endif; ?>
<?php if ($model->min_hourly_rate) : ?>
<b><?= Yii::t('bot', 'Min. hourly rate') ?>:</b> <?= $model->min_hourly_rate ?> <?= $model->currency->code ?><br/>
<br/>
<?php endif; ?>
<b><?= Yii::t('bot', 'Remote work') ?></b>: <?= $model->remote_on == Resume::REMOTE_ON ? Yii::t('bot', 'Yes') : Yii::t('bot', 'No') ; ?><br/>
<br/>
<?php if ($user) : ?>
————<br/>
<br/>
<b><?= Yii::t('bot', 'Contact') ?>:</b> <?= $user->getFullLink(); ?>
<?php endif; ?>
