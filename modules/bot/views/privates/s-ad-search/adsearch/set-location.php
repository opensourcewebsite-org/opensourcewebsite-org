<?php

use app\modules\bot\components\helpers\ExternalLink;

$locationLink = ExternalLink::getOSMLink(
    $model->location_lat,
    $model->location_lon
);

if (!empty($error)) {
    echo Yii::t('bot', 'You entered an invalid value') . ': ' . $error . '<br/><br/>';
} ?>
<?= Yii::t('bot', 'Send a location using app feature or type it') ?>
<?php
if ($model->location_lat && $model->location_lon):
    ?>
    <br/><br/>
    <b><?= Yii::t('bot', 'Location') ?>:</b> <a
    href="<?= $locationLink ?>"><?= $model->location_lat ?> <?= $model->location_lon ?></a><br/>
    <br/>
<?php
endif;
