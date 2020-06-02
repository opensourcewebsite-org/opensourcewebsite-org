<b><?= Yii::t('bot', 'Ads') ?></b><br/>
<br/>
<?= $adsPost->title ?><br/>
<br/>
<b><?= Yii::t('bot', 'Category') ?>:</b> <?= $categoryName ?><br/>
<b><?= Yii::t('bot', 'Description') ?>:</b> <?= $adsPost->description ?><br/>
<b><?= Yii::t('bot', 'Price') ?>:</b> <?= $adsPost->price / 100.0 ?><?= $currency->symbol ?><br/>
<b><?= Yii::t('bot', 'Location') ?>:</b> <?= $adsPost->location_lat ?> <?= $adsPost->location_lon ?><br/>
<b><?= Yii::t('bot', 'Delivery radius') ?>:</b> <?= $adsPost->delivery_km ?> <?= Yii::t('bot', 'km') ?><br/>
<?= $keywords ?><br/>
<br/>
<i><?= Yii::t('bot', 'Updated at') ?>: <?= (new DateTime("@" . $adsPost->updated_at))->format('d.m.Y H:i') ?></i>
