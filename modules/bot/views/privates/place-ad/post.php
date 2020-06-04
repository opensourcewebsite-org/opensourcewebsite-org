<b><?= Yii::t('bot', 'Ads') ?></b><br/>
<br/>
<?= $adsPost->title ?><br/>
<br/>
<b><?= Yii::t('bot', 'Category') ?>:</b> <?= $categoryName ?><br/>
<b><?= Yii::t('bot', 'Description') ?>:</b> <?= $adsPost->description ?><br/>
<b><?= Yii::t('bot', 'Price') ?>:</b> <?= $adsPost->price / 100.0 ?><?= $currency->symbol ?><br/>
<b><?= Yii::t('bot', 'Location') ?>:</b> <a href = "<?= $locationLink ?>"><?= $adsPost->location_lat ?> <?= $adsPost->location_lon ?></a><br/>
<b><?= Yii::t('bot', 'Delivery radius') ?>:</b> <?= $adsPost->delivery_km ?> <?= Yii::t('bot', 'km') ?><br/>
<b><?= Yii::t('bot', 'Keywords') ?>:</b> <?= $keywords ?><br/>
<br/>
<i><?= Yii::t('bot', 'This ad will be active for') ?> <?= $liveDays ?> <?= Yii::t('bot', 'days. Visit this page again before expiring time to automatically extend ad action') ?>.</i>
