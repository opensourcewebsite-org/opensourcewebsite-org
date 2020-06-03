<?= Yii::t('bot', 'Check ad correctness') ?>:<br/>
<br/>
<b><?= Yii::t('bot', 'Category') ?>:</b> <?= $categoryName ?> <br/>
<b><?= Yii::t('bot', 'Title') ?>:</b> <?= $title ?> <br/>
<b><?= Yii::t('bot', 'Description') ?>:</b> <?= $description ?> <br/>
<b><?= Yii::t('bot', 'Price') ?>:</b> <?= $price / 100.0 ?><?= $currency->symbol ?><br/>
<b><?= Yii::t('bot', 'Location') ?>:</b> <?= $locationLatitude ?> <?= $locationLongitude ?><br/>
<b><?= Yii::t('bot', 'Delivery radius') ?>:</b> <?= $radius ?> <?= Yii::t('bot', 'km') ?><br/>
<b><?= Yii::t('bot', 'Keywords') ?>:</b> <?= $keywords ?>
