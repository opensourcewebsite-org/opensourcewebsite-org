<?php

$controller = Yii::$app->controller;
$delivery_radius = $controller->field->get($controller->modelName, 'selling_delivery_radius');
?>

<b><?= Yii::t('bot', 'Send delivery radius, km') ?>
<?php if (isset(Yii::$app->controller->rule['isVirtual']) && isset($delivery_radius)) : ?>
<b>, <?= Yii::t('bot', 'or click NEXT to use existing delivery radius') ?> </b>
<?= $delivery_radius . ' km' ?>
<?php endif; ?>
.</b><br/>
<br/>
<i><?= Yii::t('bot', 'This information is used to find matches with offers from other users') ?>. <?= Yii::t('bot', 'Only you see this information') ?>.<br/>
<br/>
    - <?= Yii::t('bot', 'Your offer with a delivery radius sees other offers that contain a location or a delivery radius that intersect with your delivery radius') ?>.<br/>
    - <?= Yii::t('bot', 'Your offer without a delivery radius sees other offers that contain a delivery radius that intersect with your location') ?>.</i>
