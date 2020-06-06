🔍 <b><?= $categoryName ?></b><br/>
<br/>
<b><?= Yii::t('bot', 'Keywords') ?>:</b> <?= $keywords ?><br/>
<?php if ($adSearch->currency_id !== null && $adSearch->max_price !== null) : ?>
<b><?= Yii::t('bot', 'Max price') ?>:</b> <?= $adSearch->max_price / 100.0 ?> <?= $currency->code ?><br/>
<?php endif; ?>
<b><?= Yii::t('bot', 'Location') ?>:</b> <a href = "<?= $locationLink ?>"><?= $adSearch->location_latitude ?> <?= $adSearch->location_longitude ?></a><br/>
<b><?= Yii::t('bot', 'Pickup radius') ?>:</b> <?= $adSearch->pickup_radius ?> <?= Yii::t('bot', 'km') ?><br/>
<br/>
<?php if ($adSearch->isActive() && $showDetailedInfo) : ?>
<i><?= Yii::t('bot', 'This ad will be active for') ?> <?= $liveDays ?> <?= Yii::t('bot', 'days. Visit this page again before expiring time to automatically extend ad action') ?>.</i>
<?php endif; ?>
