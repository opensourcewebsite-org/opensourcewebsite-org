<?= $categoryName ?><br/>
<br/>
<b><?= Yii::t('bot', 'Ключевые слова') ?>:</b> <?= $keywords ?><br/>
<b><?= Yii::t('bot', 'География') ?>:</b> <?= $adsPostSearch->location_latitude ?> <?= $adsPostSearch->location_longitude ?><br/>
<b><?= Yii::t('bot', 'Радиус поиска') ?>:</b> <?= $adsPostSearch->radius ?> <?= Yii::t('bot', 'км') ?><br/>
<br/>
<i><?= Yii::t('bot', 'Обновлено') ?>: <?= (new DateTime("@" . $adsPostSearch->updated_at))->format('d.m.Y H:i') ?></i>
