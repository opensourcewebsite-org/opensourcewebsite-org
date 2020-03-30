<?php

use app\models\User;
?>
<b><?= Yii::t('bot', 'Your Profile') ?></b><br/>
<br/>
<?php
/** @var \TelegramBot\Api\Types\User $profile */
if (isset($firstName)) {
    echo Yii::t('bot', 'First Name') . ': ' . $firstName . '<br/>';
}
if (isset($lastName)) {
    echo Yii::t('bot', 'Last Name') . ': ' . $lastName . '<br/>';
}
if (isset($username)) {
    echo Yii::t('bot', 'Telegram Username') . ': @' . $username . '<br/>';
}
if (isset($gender)) {
    echo Yii::t('bot', 'Gender') . ': ' . Yii::t('bot', ($gender == User::MALE ? 'Male' : 'Female')) . '<br/>';
}
if (isset($birthday)) {
    echo Yii::t('bot', 'Birthday') . ': ' . $birthday . '<br/>';
}
if (isset($currency)) {
    echo Yii::t('bot', 'Currency') . ': ' .  $currency . '<br/>';
}
if (isset($language)) {
    echo Yii::t('bot', 'Interface language') . ': ' .  $interfaceLanguage . '<br/>';
}
if (isset($timezone)) {
     echo Yii::t('bot', 'Timezone') . ': ' . $timezone . '<br/>';
}
?>
<? if (!empty($languages)) : ?>
<br/>
<?= Yii::t('bot', 'Languages') ?>:<br/>
<? foreach ($languages as $language) : ?>
<?= $language ?><br/>
<? endforeach; ?>
<? endif; ?>
<? if (!empty($citizenships)) : ?>
<br/>
<?= Yii::t('bot', 'Citizenships') ?>:<br/>
<? foreach ($citizenships as $citizenship) : ?>
<?= $citizenship ?><br/>
<? endforeach; ?>
<? endif; ?>
