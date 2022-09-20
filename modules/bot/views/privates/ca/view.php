<?php

use app\modules\bot\components\helpers\Emoji;
use app\components\helpers\Html;
use app\modules\bot\components\helpers\ExternalLink;

?>
<?= Emoji::CE_ORDER ?> <b><?= Yii::t('bot', 'Cash Exchange') ?></b><br/>
<br/>
<b><?= Yii::t('bot', 'Sell') ?></b>: <?= $model->sellingCurrency->code ?><br/>
<b><?= Yii::t('bot', 'Buy') ?></b>: <?= $model->buyingCurrency->code ?><br/>
<b><?= Yii::t('bot', 'Location') ?></b>: <?= ExternalLink::getOSMFullLink($model->selling_location_lat, $model->selling_location_lon) ?><br/>
<?php if ($model->selling_delivery_radius > 0) : ?>
<b><?= Yii::t('bot', 'Search radius') ?></b>: <?= $model->selling_delivery_radius ?> <?= Yii::t('bot', 'km') ?><br/>
<?php endif; ?>
<br/>
<i><?= Yii::t('bot', 'Only you see this information') ?></i>.<br/>
