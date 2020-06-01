<?= Yii::t('bot', 'Проверьте правильность объявления:') ?> <br/>
<br/>
<b><?= Yii::t('bot', 'Категория') ?>:</b> <?= $categoryName ?> <br/>
<b><?= Yii::t('bot', 'Название') ?>:</b> <?= $title ?> <br/>
<b><?= Yii::t('bot', 'Описание') ?>:</b> <?= $description ?> <br/>
<b><?= Yii::t('bot', 'Цена') ?>:</b> <?= $price / 100.0 ?><?= $currency->symbol ?><br/>
<b><?= Yii::t('bot', 'География') ?>:</b> <?= $locationLatitude ?> <?= $locationLongitude ?><br/>
<b><?= Yii::t('bot', 'Радиус доставки') ?>:</b> <?= $radius ?> <?= Yii::t('bot', 'км') ?><br/>
<?= $keywords ?>
