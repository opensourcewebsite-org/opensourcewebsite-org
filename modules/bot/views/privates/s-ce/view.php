<?php

use app\models\CurrencyExchangeOrder;
use app\modules\bot\components\helpers\Emoji;

?>
<b><?= Emoji::CURRENCY_EXCHANGE_ORDER . ' ' . Yii::t('bot', 'Swap') ?>: <?= $model->getTitle() ?></b><br/>
<br/>
<b><?= Yii::t('bot', 'Selling currency') ?>:</b> <?= $model->sellingCurrency->code ?><br/>
<br/>
<b><?= Yii::t('bot', 'Exchange rate') ?>:</b> <?= $model->cross_rate_on ? Yii::t('bot', 'Cross rate') : (float)$model->selling_rate ?><br/>
<br/>
<?php if ($model->hasAmount()) : ?>
<b><?= Yii::t('bot', 'Amount') ?>:</b> <?= $model->getSellingCurrencyMinAmount() ?> - <?= $model->getSellingCurrencyMaxAmount() ?><br/>
<br/>
<?php endif; ?>
<b><?= Yii::t('bot', 'Payment methods') ?>:</b><br/>
<?php if ($model->selling_cash_on) : ?>
<?= Yii::t('bot', 'Cash') ?><br/>
<?php endif; ?>
<?php foreach ($sellingPaymentMethods as $paymentMethod) : ?>
<?= $paymentMethod ?><br/>
<?php endforeach; ?>
<br/>
————<br/>
<br/>
<b><?= Yii::t('bot', 'Buying currency') ?>:</b> <?= $model->buyingCurrency->code ?><br/>
<br/>
<b><?= Yii::t('bot', 'Reverse exchange rate') ?>:</b> <?= $model->cross_rate_on ? Yii::t('bot', 'Cross rate') : (float)$model->buying_rate ?><br/>
<br/>
<b><?= Yii::t('bot', 'Payment methods') ?>:</b><br/>
<?php if ($model->buying_cash_on) : ?>
<?= Yii::t('bot', 'Cash') ?><br/>
<?php endif; ?>
<?php foreach ($buyingPaymentMethods as $paymentMethod) : ?>
<?= $paymentMethod ?><br/>
<?php endforeach; ?>
<br/>
<?php if ($model->selling_cash_on || $model->buying_cash_on) : ?>
<?php if ($model->location_lat && $model->location_lon) : ?>
————<br/>
<br/>
<b><?= Yii::t('bot', 'Location') ?>:</b> <a href = "<?= $locationLink ?>"><?= $model->location_lat ?> <?= $model->location_lon ?></a><br/>
<br/>
<?php if ($model->delivery_radius > 0) : ?>
<b><?= Yii::t('bot', 'Delivery radius') ?>:</b> <?= $model->delivery_radius ?> <?= Yii::t('bot', 'km') ?><br/>
<br/>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
<?php if ($model->isActive()) : ?>
————<br/>
<br/>
<i><?= Yii::t('bot', 'You will receive a notification in case of matches with offers of other users') ?>.</i>
<?php endif; ?>
