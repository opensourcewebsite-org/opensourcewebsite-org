<b><?= $adsPost->title ?></b><br/>
<b><?= Yii::t('bot', 'Статус') ?>:</b> <?= $statusName ?><br/>
<b><?= Yii::t('bot', 'Категория') ?>:</b> <?= $categoryName ?><br/>
<b><?= Yii::t('bot', 'Описание') ?>:</b> <?= $adsPost->description ?><br/>
<b><?= Yii::t('bot', 'Цена') ?>:</b> <?= $adsPost->price ?><br/>
<b><?= Yii::t('bot', 'География') ?>:</b> <?= $adsPost->location_lat ?> <?= $adsPost->location_lon ?><br/>
<b><?= Yii::t('bot', 'Контакты') ?>:</b> <a href = "tg://user?id=<?= $telegramUser->provider_user_id ?>"> <?= $telegramUser->provider_user_first_name ?> <?= $telegramUser->provider_user_last_name ?></a><br/>
<?= $keywords ?><br/>
<br/>
<?= Yii::t('bot', 'Статистика объявления') ?><br/>
<?= Yii::t('bot', 'Просмотрено') ?>: 1<br/>
<?= Yii::t('bot', 'Рейтинг') ?>: 1
