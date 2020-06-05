💰 <b><?= $categoryName ?></b><br/>
<br/>
<?= $adsPost->title ?><br/>
<br/>
<b><?= Yii::t('bot', 'Keywords') ?>:</b> <?= $keywords ?><br/>
<b><?= Yii::t('bot', 'Description') ?>:</b> <?= $adsPost->description ?><br/>
<b><?= Yii::t('bot', 'Price') ?>:</b> <?= $adsPost->price / 100.0 ?> <?= $currency->code ?><br/>
<b><?= Yii::t('bot', 'Location') ?>:</b> <a href = "<?= $locationLink ?>"><?= $adsPost->location_lat ?> <?= $adsPost->location_lon ?></a><br/>
<?php if ($adsPost->delivery_km > 0) : ?>
<b><?= Yii::t('bot', 'Delivery radius') ?>:</b> <?= $adsPost->delivery_km ?> <?= Yii::t('bot', 'km') ?><br/>
<?php endif; ?>
<br/>
<?php if ($adsPost->isActive()) : ?>
<i><?= Yii::t('bot', 'This ad will be active for') ?> <?= $liveDays ?> <?= Yii::t('bot', 'days. Visit this page again before expiring time to automatically extend ad action') ?>.</i>
<?php endif; ?>
