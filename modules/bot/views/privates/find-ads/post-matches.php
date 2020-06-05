<b>💰 <?= $categoryName ?></b><br/>
<br/>
<?= $adsPost->title ?><br/>
<br/>
<b><?= Yii::t('bot', 'Description') ?>:</b> <?= $adsPost->description ?><br/>
<b><?= Yii::t('bot', 'Price') ?>:</b> <?= $adsPost->price / 100.0 ?><?= $currency->symbol ?><br/>
<b><?= Yii::t('bot', 'Location') ?>:</b> <a href = "<?= $locationLink ?>"><?= $adsPost->location_lat ?> <?= $adsPost->location_lon ?></a><br/>
<?php if ($adsPost->delivery_km > 0) : ?>
<b><?= Yii::t('bot', 'Delivery radius') ?>:</b> <?= $adsPost->delivery_km ?> <?= Yii::t('bot', 'km') ?><br/>
<?php endif; ?>
<b><?= Yii::t('bot', 'Keywords') ?>:</b> <?= $keywords ?><br/>
<br/>
<b><?= Yii::t('bot', 'Contacts') ?>:</b> <a href = "tg://user?id=<?= $user->provider_user_id ?>"><?= $user->provider_user_first_name ?> <?= $user->provider_user_last_name ?></a>
