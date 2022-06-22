<?php

use app\models\Resume;
use app\modules\bot\components\helpers\Emoji;

?>
<b><?= Emoji::JO_RESUME . ' ' . Yii::t('bot', 'Resume') ?>: <?= $model->name ?></b><br/>
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
<?php if ($model->location_lat && $model->location_lon) : ?>
<b><?= Yii::t('bot', 'Location') ?>:</b> <a href = "<?= $locationLink ?>"><?= $model->location_lat ?> <?= $model->location_lon ?></a><br/>
<br/>
<?php if ($model->search_radius > 0) : ?>
<b><?= Yii::t('bot', 'Search radius') ?>:</b> <?= $model->search_radius ?> <?= Yii::t('bot', 'km') ?><br/>
<br/>
<?php endif; ?>
<?php endif; ?>
<?php if ($model->isActive()) : ?>
————<br/>
<br/>
<i><?= Yii::t('bot', 'You will receive a notification in case of matches with offers of other users') ?>.</i>
<?php endif; ?>
