🔍 <b><?= $categoryName ?></b><br/>
<br/>
<b><?= Yii::t('bot', 'Keywords') ?>:</b> <?= $keywords ?><br/>
<?php if ($adsPostSearch->currency_id !== null && $adsPostSearch->max_price !== null) : ?>
<b><?= Yii::t('bot', 'Max price') ?>:</b> <?= $adsPostSearch->max_price / 100.0 ?> <?= $currency->code ?><br/>
<?php endif; ?>
<b><?= Yii::t('bot', 'Location') ?>:</b> <a href = "<?= $locationLink ?>"><?= $adsPostSearch->location_latitude ?> <?= $adsPostSearch->location_longitude ?></a><br/>
<b><?= Yii::t('bot', 'Pickup radius') ?>:</b> <?= $adsPostSearch->radius ?> <?= Yii::t('bot', 'km') ?><br/>
<br/>
<?php if ($adsPostSearch->isActive()) : ?>
<i><?= Yii::t('bot', 'This ad will be active for') ?> <?= $liveDays ?> <?= Yii::t('bot', 'days. Visit this page again before expiring time to automatically extend ad action') ?>.</i>
<?php endif; ?>
