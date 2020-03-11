<?php

use app\models\User;
?>
<b><?= \Yii::t('bot', 'Your Profile') ?></b><br/>
<br/>
<?php
/** @var \TelegramBot\Api\Types\User $profile */
if (!empty($firstName)) {
    echo \Yii::t('bot', 'First Name') . ": " . $firstName . "<br/>";
}
if (!empty($lastName)) {
    echo \Yii::t('bot', 'Last Name') . ": " . $lastName . "<br/>";
}
if (!empty($username)) {
    echo \Yii::t('bot', 'Telegram Username') . ": @" . $username . "<br/>";
}
if (!empty($gender)) {
    echo \Yii::t('bot', 'Gender') . ": " . \Yii::t('bot', ($gender == User::MALE ? "Male" : "Female")) . "<br/>";
}
if (!empty($birthday)) {
    echo \Yii::t('bot', 'Birthday') . ": " . $birthday . "<br/>";
}
if (!empty($currency)) {
    echo \Yii::t('bot', 'Currency') . ": " .  $currency . "<br/>";
}
if (!empty($language)) {
    echo \Yii::t('bot', 'Language') . ": " .  $language . "<br/>";
}
if (!empty($timezone)) {
	echo \Yii::t('bot', 'Timezone') . ": " . $timezone . "<br/>";
}
