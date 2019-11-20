<b><?= \Yii::t('bot', 'Your Profile') ?></b><br/><br/>
<?php
/** @var \TelegramBot\Api\Types\User $profile */
if (!empty($profile->getFirstName())) {
    echo \Yii::t('bot', 'First Name') . ": " . $profile->getFirstName() . "<br/>";
}
if (!empty($profile->getLastName())) {
    echo \Yii::t('bot', 'Last Name') . ": " . $profile->getLastName() . "<br/>";
}
if (!empty($profile->getUsername())) {
    echo \Yii::t('bot', 'Telegram Username') . ": @" . $profile->getUsername() . "<br/>";
}
