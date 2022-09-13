<?php

use app\modules\bot\components\helpers\Emoji;
use app\components\helpers\Html;
use app\modules\bot\components\helpers\ExternalLink;

?>
<?= Yii::t('bot', 'We could not find any match for the following criteria') . ':' ?> <br/>
<br/>
<b><?= Yii::t('bot', 'Sell') ?></b>: <?= $model->sellingCurrency->code ?><br/>
<b><?= Yii::t('bot', 'Buy') ?></b>: <?= $model->buyingCurrency->code ?><br/>
<b><?= Yii::t('bot', 'Location') ?></b>: <?= ExternalLink::getOSMFullLink($model->selling_location_lat, $model->selling_location_lon) ?><br/>
<b><?= Yii::t('bot', 'Delivery radius') ?></b>: <?= $model->selling_delivery_radius ?> <?= Yii::t('bot', 'km') ?><br/>

