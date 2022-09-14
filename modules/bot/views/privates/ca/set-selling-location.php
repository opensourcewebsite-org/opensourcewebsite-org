<?php
use app\modules\bot\components\helpers\ExternalLink;

$controller = Yii::$app->controller;
$location_lat = $controller->field->get($controller->modelName, 'selling_location_lat');
$location_lon = $controller->field->get($controller->modelName, 'selling_location_lon');
?>

<b><?= Yii::t('bot', 'Send location using app feature or type it in format «Latitude Longitude»') ?>
<?php if (isset(Yii::$app->controller->rule['isVirtual']) && isset($location_lat) && isset($location_lon)) : ?>
<b>, <?= Yii::t('bot', 'or click NEXT to use existing location') ?> </b>
<?= ExternalLink::getOSMFullLink($model->selling_location_lat, $model->selling_location_lon)?>
<?php endif; ?>
.</b><br/>
<br/>
<i><?= Yii::t('bot', 'This information is used to find matches with offers from other users') ?>. <?= Yii::t('bot', 'Only you see this information') ?>.</i><br/>
