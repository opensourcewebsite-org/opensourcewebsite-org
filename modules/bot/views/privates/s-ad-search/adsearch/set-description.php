<?php
if (!empty($error)) {
    echo Yii::t('bot', 'You entered an invalid value') . ': ' . $error . '<br/><br/>';
} ?>
<?= Yii::t('bot', 'Send a description') ?>
<br/><br/>
<b><?= nl2br($model->description); ?></b>
