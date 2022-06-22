<?php

use app\modules\bot\components\helpers\Emoji;

?>
<b><?= Emoji::AD_SEARCH . ' ' . Yii::t('bot', $model->getSectionName()) ?>: <?= $model->title ?></b><br/>
<br/>
<?php if ($model->description !== null) : ?>
<?= nl2br($model->description); ?><br/>
<br/>
<?php endif; ?>
<?php if ($keywords != '') : ?>
# <i><?= $keywords ?></i><br/>
<br/>
<?php endif; ?>
<?php if ($model->max_price) : ?>
<b><?= Yii::t('bot', 'Max price') ?>:</b> <?= $model->max_price ?> <?= $model->currency->code ?><br/>
<br/>
<?php endif; ?>
<b><?= Yii::t('bot', 'Location') ?>:</b> <a href = "<?= $locationLink ?>"><?= $model->location_lat ?> <?= $model->location_lon ?></a><br/>
<br/>
<?php if ($model->pickup_radius > 0) : ?>
<b><?= Yii::t('bot', 'Pickup radius') ?>:</b> <?= $model->pickup_radius ?> <?= Yii::t('bot', 'km') ?><br/>
<br/>
<?php endif; ?>
<?php if ($model->isActive()) : ?>
————<br/>
<br/>
<i><?= Yii::t('bot', 'You will receive a notification in case of matches with offers of other users') ?>.</i>
<?php endif; ?>
