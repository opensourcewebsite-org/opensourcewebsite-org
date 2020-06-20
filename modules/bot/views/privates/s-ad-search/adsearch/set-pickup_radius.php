<?php
if (!empty($error)) {
    echo Yii::t('bot', 'You entered an invalid value') . ': ' . $error . '<br/><br/>';
} ?>
<?= Yii::t('bot', 'Send pickup radius, km') ?>
