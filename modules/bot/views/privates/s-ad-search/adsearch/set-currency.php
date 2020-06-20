<?php
if (!empty($error)) {
    echo Yii::t('bot', 'You entered an invalid value') . ': ' . $error . '<br/><br/>';
} ?>
<b><?= Yii::t('bot', 'Choose your currency or type it') ?>:</b>
