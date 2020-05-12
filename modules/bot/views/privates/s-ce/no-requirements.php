🏗 <b><?= Yii::t('bot', 'Currency Exchange') ?> (<?= Yii::t('bot', 'in development') ?>)</b><br/>
<br/>
<?= Yii::t('bot', 'To start using this service follow these steps') ?>:<br/>

<?php
if (!$telegramUser->location_lon && !$telegramUser->location_lat) {
    echo Yii::t('bot', 'Send your location');
}
?>
<br/>
/my_location - <?= Yii::t('bot', 'Location') ?><br/>
<br/>
<?php
if (!$telegramUser->provider_user_name) {
    echo Yii::t('bot', 'Set your nickname in your Telegram account');
}
?>
<br/>/my_profile - <?= Yii::t('bot', 'Profile') ?><br/>
