<?php

use app\models\Resume;

?>
<b><?= Yii::t('bot', 'Resume') ?>: <?= $name ?></b><br/>
<br/>
<?php if ($keywords != '') : ?>
# <i><?= $keywords ?></i><br/>
<br/>
<?php endif; ?>
<?php if ($skills) : ?>
<b><?= Yii::t('bot', 'Skills') ?>:</b><br/>
<br/>
<?= nl2br($skills) ?><br/>
<br/>
<?php endif; ?>
<?php if ($experiences) : ?>
<b><?= Yii::t('bot', 'Experiences') ?>:</b><br/>
<br/>
<?= nl2br($experiences) ?><br/>
<br/>
<?php endif; ?>
<?php if ($expectations) : ?>
<b><?= Yii::t('bot', 'Expectations') ?>:</b><br/>
<br/>
<?= nl2br($expectations) ?><br/>
<br/>
<?php endif; ?>
<?php if ($hourlyRate) : ?>
<b><?= Yii::t('bot', 'Min. hourly rate') ?>:</b> <?= $hourlyRate ?> <?= $currencyCode ?><br/>
<br/>
<?php endif; ?>
<b><?= Yii::t('bot', 'Remote work') ?></b>: <?= $remote_on == Resume::REMOTE_ON ? Yii::t('bot', 'Yes') : Yii::t('bot', 'No') ; ?><br/>
<br/>
<?php if ($model->location_lat && $model->location_lon) : ?>
<b><?= Yii::t('bot', 'Location') ?>:</b> <a href = "<?= $locationLink ?>"><?= $model->location_lat ?> <?= $model->location_lon ?></a><br/>
<br/>
<?php if ($model->search_radius > 0) : ?>
<b><?= Yii::t('bot', 'Search radius') ?>:</b> <?= $model->search_radius ?> <?= Yii::t('bot', 'km') ?><br/>
<br/>
<?php endif; ?>
<?php endif; ?>
<?php if ($isActive) : ?>
————<br/>
<br/>
<i><?= Yii::t('bot', 'You will receive a notification in case of matches with offers of other users') ?>.</i>
<?php endif; ?>
