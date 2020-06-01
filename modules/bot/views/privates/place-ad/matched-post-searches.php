<?= Yii::t('bot', $categoryName) ?><br/>
<br/>
<b><?= Yii::t('bot', 'Категория') ?>:</b> <?= Yii::t('bot', $keywords) ?><br/>
<b><?= Yii::t('bot', 'География') ?>:</b> <?= $adsPostSearch->location_latitude ?> <?= $adsPostSearch->location_longitude ?><br/>
<b><?= Yii::t('bot', 'Радиус поиска') ?>:</b> <?= $adsPostSearch->radius ?> <?= Yii::t('bot', 'км') ?><br/>
<br/>
<b><?= Yii::t('bot', 'Контакты') ?>:</b> <a href = "tg://user?id=<?= $user->provider_user_id?>"> <?= $user->provider_user_first_name ?> <?= $user->provider_user_last_name ?></a>
