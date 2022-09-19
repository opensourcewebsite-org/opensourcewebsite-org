<?php

use app\modules\bot\components\helpers\Emoji;
use app\components\helpers\Html;
use app\modules\bot\components\helpers\ExternalLink;

?>
<?= Emoji::CE_ORDER ?> <b><?= Yii::t('bot', 'Order') ?>: #<?= $model->id ?> <?= $model->getTitle() ?></b><br/>
<br/>
<b><?= Yii::t('bot', 'Sell') ?></b>: <?= $model->sellingCurrency->code ?><br/>
<br/>
<b><?= Yii::t('bot', 'Buy') ?></b>: <?= $model->buyingCurrency->code ?><br/>
<br/>
<b><?= Yii::t('bot', 'Exchange rate') ?></b>: <?= $model->selling_rate ?: '∞' ?><br/>
<br/>
<b><?= Yii::t('bot', 'Inverse rate') ?></b>: <?= $model->buying_rate ?: '∞' ?><br/>
<br/>
<b><?= Yii::t('ce_order', 'Limits') ?></b>: <?= $model->getFormatLimits() ?><br/>
————<br/>
<b><?= Yii::t('ce_order', 'Selling payment methods') ?></b>:<br/>
<?php if ($model->selling_cash_on) : ?>
  • <?= Yii::t('bot', 'Cash') ?><br/>
  <?= Emoji::HIDDEN ?> <i><?= Yii::t('bot', 'Location') ?>: <?= ExternalLink::getOSMFullLink($model->selling_location_lat, $model->selling_location_lon) ?></i><br/>
<?php if ($model->selling_delivery_radius > 0) : ?>
    <?= Emoji::HIDDEN ?> <i><?= Yii::t('bot', 'Delivery radius') ?>: <?= $model->selling_delivery_radius ?> <?= Yii::t('bot', 'km') ?></i><br/>
<?php endif; ?>
<?php endif; ?>
<?php foreach ($model->sellingPaymentMethods as $method) : ?>
  • <?= $method->url ? Html::a($method->name, $method->url) : $method->name; ?><br/>
<?php endforeach; ?>
————<br/>
<b><?= Yii::t('ce_order', 'Buying payment methods') ?></b>:<br/>
<?php if ($model->buying_cash_on) : ?>
  • <?= Yii::t('bot', 'Cash') ?><br/>
  <?= Emoji::HIDDEN ?> <i><?= Yii::t('bot', 'Location') ?>: <?= ExternalLink::getOSMFullLink($model->buying_location_lat, $model->buying_location_lon) ?></i><br/>
<?php if ($model->buying_delivery_radius > 0) : ?>
    <?= Emoji::HIDDEN ?> <i><?= Yii::t('bot', 'Delivery radius') ?>: <?= $model->buying_delivery_radius ?> <?= Yii::t('bot', 'km') ?></i><br/>
<?php endif; ?>
<?php endif; ?>
<?php foreach ($model->buyingPaymentMethods as $method) : ?>
  • <?= $method->url ? Html::a($method->name, $method->url) : $method->name; ?><br/>
<?php endforeach; ?>
<?php if ($user = $model->user->botUser) : ?>
————<br/>
<b><?= Yii::t('bot', 'Contact') ?></b>: <?= $user->getFullLink(); ?>
<?php endif; ?>
