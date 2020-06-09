💰 <b><?= $sectionName ?></b><br/>
<br/>
<?= $adOffer->title ?><br/>
<br/>
<b><?= Yii::t('bot', 'Keywords') ?>:</b> <?= $keywords ?><br/>
<?php if ($adOffer->description !== null) : ?>
<b><?= Yii::t('bot', 'Description') ?>:</b> <?= $adOffer->description ?><br/>
<?php endif; ?>
<b><?= Yii::t('bot', 'Price') ?>:</b> <?= $adOffer->price ?> <?= $currency->code ?><br/>
<b><?= Yii::t('bot', 'Location') ?>:</b> <a href = "<?= $locationLink ?>"><?= $adOffer->location_lat ?> <?= $adOffer->location_lon ?></a><br/>
<?php if ($adOffer->delivery_radius > 0) : ?>
<b><?= Yii::t('bot', 'Delivery radius') ?>:</b> <?= $adOffer->delivery_radius ?> <?= Yii::t('bot', 'km') ?><br/>
<?php endif; ?>
<br/>
<?php if ($adOffer->isActive() && $showDetailedInfo) : ?>
<i><?= Yii::t('bot', 'This ad will be active for') ?> <?= $liveDays ?> <?= Yii::t('bot', 'days. Visit this page again before expiring time to automatically extend ad action') ?>.</i>
<?php endif; ?>
