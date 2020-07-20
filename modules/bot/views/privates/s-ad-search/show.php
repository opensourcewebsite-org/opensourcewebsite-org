🔍 <b><?= $sectionName ?></b> - <b><?= $adSearch->title ?></b><br/>
<br/>
<?php if ($adSearch->description !== null) : ?>
<?= nl2br($adSearch->description); ?><br/>
<br/>
<?php endif; ?>
<?php if ($keywords != '') : ?>
# <i><?= $keywords ?></i><br/>
<br/>
<?php endif; ?>
<?php if ($adSearch->currency_id !== null && $adSearch->max_price !== null) : ?>
<b><?= Yii::t('bot', 'Max price') ?>:</b> <?= $adSearch->max_price ?> <?= $currency->code ?><br/>
<br/>
<?php endif; ?>
<b><?= Yii::t('bot', 'Location') ?>:</b> <a href = "<?= $locationLink ?>"><?= $adSearch->location_lat ?> <?= $adSearch->location_lon ?></a><br/>
<br/>
<?php if ($adSearch->pickup_radius > 0) : ?>
<b><?= Yii::t('bot', 'Pickup radius') ?>:</b> <?= $adSearch->pickup_radius ?> <?= Yii::t('bot', 'km') ?><br/>
<br/>
<?php endif; ?>
<?php if ($adSearch->isActive() && $showDetailedInfo) : ?>
————<br/>
<br/>
<i><?= Yii::t('bot', 'You will receive a notification in case of matches with offers of other users') ?>.</i>
<?php endif; ?>
