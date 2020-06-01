<?= $adsPost->title ?><br/>
<br/>
<b><?= Yii::t('bot', 'Категория') ?>:</b> <?= $categoryName ?><br/>
<b><?= Yii::t('bot', 'Описание') ?>:</b> <?= $adsPost->description ?><br/>
<b><?= Yii::t('bot', 'Цена') ?>:</b> <?= $adsPost->price / 100.0 ?><?= $currency->symbol ?><br/>
<b><?= Yii::t('bot', 'География') ?>:</b> <?= $adsPost->location_lat ?> <?= Yii::t('bot', $adsPost->location_lon) ?><br/>
<b><?= Yii::t('bot', 'Радиус доставки') ?>:</b> <?= $adsPost->delivery_km ?> <?= Yii::t('bot', 'км') ?><br/>
<?= $keywords ?><br/>
<br/>
<b><?= Yii::t('bot', 'Контакты') ?>:</b> <a href = "tg://user?id=<?= $user->provider_user_id ?>"><?= $user->provider_user_first_name ?> <?= $user->provider_user_last_name ?></a>
