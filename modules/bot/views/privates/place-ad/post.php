<b><?= Yii::t('bot', 'Объявления') ?></b><br/>
<br/>
<?= $adsPost->title ?><br/>
<br/>
<b><?= Yii::t('bot', 'Категория') ?>:</b> <?= $categoryName ?><br/>
<b><?= Yii::t('bot', 'Описание') ?>:</b> <?= $adsPost->description ?><br/>
<b><?= Yii::t('bot', 'Цена') ?>:</b> <?= $adsPost->price / 100.0 ?><?= $currency->symbol ?><br/>
<b><?= Yii::t('bot', 'География') ?>:</b> <?= $adsPost->location_lat ?> <?= $adsPost->location_lon ?><br/>
<b><?= Yii::t('bot', 'Радиус доставки') ?>:</b> <?= $adsPost->delivery_km ?> <?= Yii::t('bot', 'км') ?><br/>
<?= $keywords ?><br/>
<br/>
<i><?= Yii::t('bot', 'Обновлено') ?>: <?= (new DateTime("@" . $adsPost->updated_at))->format('d.m.Y H:i') ?></i>
