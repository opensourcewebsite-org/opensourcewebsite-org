💰 <b><?= $categoryName ?></b><br/>
<br/>
<?= $adOrder->title ?><br/>
<br/>
<b><?= Yii::t('bot', 'Keywords') ?>:</b> <?= $keywords ?><br/>
<b><?= Yii::t('bot', 'Description') ?>:</b> <?= $adOrder->description ?><br/>
<b><?= Yii::t('bot', 'Price') ?>:</b> <?= $adOrder->price ?> <?= $currency->code ?><br/>
<b><?= Yii::t('bot', 'Location') ?>:</b> <a href = "<?= $locationLink ?>"><?= $adOrder->location_lat ?> <?= $adOrder->location_lon ?></a><br/>
<?php if ($adOrder->delivery_radius > 0) : ?>
<b><?= Yii::t('bot', 'Delivery radius') ?>:</b> <?= $adOrder->delivery_radius ?> <?= Yii::t('bot', 'km') ?><br/>
<?php endif; ?>
<br/>
<?php if ($adOrder->isActive() && $showDetailedInfo) : ?>
<i><?= Yii::t('bot', 'This ad will be active for') ?> <?= $liveDays ?> <?= Yii::t('bot', 'days. Visit this page again before expiring time to automatically extend ad action') ?>.</i>
<?php endif; ?>
