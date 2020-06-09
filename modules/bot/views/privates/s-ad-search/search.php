🔍 <b><?= $sectionName ?></b><br/>
<br/>
<?= $adSearch->title ?><br/>
<br/>
<?php if ($adSearch->description !== null) : ?>
<b><?= Yii::t('bot', 'Description') ?>:</b> <?= $adSearch->description ?><br/>
<?php endif; ?>
<b><?= Yii::t('bot', 'Keywords') ?>:</b> <?= $keywords ?><br/>
<?php if ($adSearch->currency_id !== null && $adSearch->max_price !== null) : ?>
<b><?= Yii::t('bot', 'Max price') ?>:</b> <?= $adSearch->max_price ?> <?= $currency->code ?><br/>
<?php endif; ?>
<b><?= Yii::t('bot', 'Location') ?>:</b> <a href = "<?= $locationLink ?>"><?= $adSearch->location_lat ?> <?= $adSearch->location_lon ?></a><br/>
<?php if ($adSearch->pickup_radius > 0) : ?>
<b><?= Yii::t('bot', 'Pickup radius') ?>:</b> <?= $adSearch->pickup_radius ?> <?= Yii::t('bot', 'km') ?><br/>
<?php endif; ?>
<br/>
<?php if ($adSearch->isActive() && $showDetailedInfo) : ?>
<i><?= Yii::t('bot', 'This ad will be active for') ?> <?= $liveDays ?> <?= Yii::t('bot', 'days. Visit this page again before expiring time to automatically extend ad action') ?>.</i>
<?php endif; ?>
