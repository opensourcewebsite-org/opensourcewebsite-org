<?php
if (!empty($error)) {
    echo Yii::t('bot', 'You entered an invalid value') . ': ' . $error . '<br/><br/>';
} ?>
<?= Yii::t('bot', 'Send a keywords separated by comma, dot or new line') ?>
