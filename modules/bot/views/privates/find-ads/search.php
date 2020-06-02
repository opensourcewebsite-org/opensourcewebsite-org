<?= $categoryName ?><br/>
<br/>
<b><?= Yii::t('bot', 'Keywords') ?>:</b> <?= $keywords ?><br/>
<b><?= Yii::t('bot', 'Location') ?>:</b> <?= $adsPostSearch->location_latitude ?> <?= $adsPostSearch->location_longitude ?><br/>
<b><?= Yii::t('bot', 'Search radius') ?>:</b> <?= $adsPostSearch->radius ?> <?= Yii::t('bot', 'km') ?><br/>
<br/>
<i><?= Yii::t('bot', 'Updated at') ?>: <?= (new DateTime("@" . $adsPostSearch->updated_at))->format('d.m.Y H:i') ?></i>
