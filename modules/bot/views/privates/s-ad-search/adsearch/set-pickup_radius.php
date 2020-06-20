<?php
if (!empty($error)) {
    echo Yii::t('bot', 'You entered an invalid value') . ': ' . $error . '<br/><br/>';
} ?>
<?= Yii::t('bot', 'Send pickup radius, km') ?>
<?php
if ($model->pickup_radius):
    ?>
    <br/><br/>
    <?= $model->pickup_radius ?> <?= Yii::t('bot', 'km') ?>
<?php
endif;
