
<b><?= Yii::t('bot', 'Your Profile') ?></b><br/>
<br/>
<?php
if (isset($firstName)) {
    echo Yii::t('bot', 'First Name') . ': ' . $firstName . '<br/>';
}
if (isset($lastName)) {
    echo Yii::t('bot', 'Last Name') . ': ' . $lastName . '<br/>';
}
if (isset($username)) {
    echo Yii::t('bot', 'Telegram') . ': @' . $username . '<br/>';
}
if (isset($gender)) {
    echo Yii::t('bot', 'Gender') . ': ' . Yii::t('bot', $gender) . '<br/>';
}
if (isset($birthday)) {
    echo Yii::t('bot', 'Birthday') . ': ' . $birthday . '<br/>';
}
if (isset($currency)) {
    echo Yii::t('bot', 'Currency') . ': ' .  $currency . '<br/>';
}
if (isset($timezone)) {
     echo Yii::t('bot', 'Timezone') . ': ' . $timezone . '<br/>';
}
if (!empty($languages)) {
    echo '<br/>';
    echo Yii::t('bot', 'Languages') . ':<br/>';
    foreach ($languages as $language) {
        echo $language . '<br/>';
    }
}
if (!empty($citizenships)) {
    echo '<br/>';
    echo Yii::t('bot', 'Citizenship') . ':<br/>';
    foreach ($citizenships as $citizenship) {
        echo $citizenship . '<br/>';
    }
}
