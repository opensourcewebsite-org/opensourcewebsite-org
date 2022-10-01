<?php

use app\modules\bot\components\helpers\Emoji;
use app\components\helpers\Html;

?>
<?= $isNewMatch ? Emoji::NEW1 . ' ' : '' ?><?= Emoji::CE_ORDER ?> <b><?= Yii::t('bot', 'Order') ?>: #<?= $model->id ?> <?= $model->getTitle() ?></b><br/>
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
<br/>
<b><?= Yii::t('ce_order', 'Selling payment methods') ?></b>:<br/>
<?php if ($model->selling_cash_on) : ?>
  • <?= Yii::t('bot', 'Cash') ?><br/>
<?php endif; ?>
<?php foreach ($model->sellingPaymentMethods as $method) : ?>
  • <?= $method->url ? Html::a($method->name, $method->url) : $method->name; ?><br/>
<?php endforeach; ?>
<br/>
<b><?= Yii::t('ce_order', 'Buying payment methods') ?></b>:<br/>
<?php if ($model->buying_cash_on) : ?>
  • <?= Yii::t('bot', 'Cash') ?><br/>
<?php endif; ?>
<?php foreach ($model->buyingPaymentMethods as $method) : ?>
  • <?= $method->url ? Html::a($method->name, $method->url) : $method->name; ?><br/>
<?php endforeach; ?>
<?php if ($globalUser = $model->user) : ?>
————<br/>
<?php if ($user = $globalUser->botUser) : ?>
<?= Emoji::RIGHT ?> <?= $user->getFullLink(); ?><br/>
<br/>
<?php endif; ?>
<b>OSW ID</b>: #<?= $globalUser->getIdFullLink() ?><?= ($globalUser->username ? ' @' . $globalUser->username : '') ?><br/>
<b><?= Yii::t('user', 'Rank') ?></b>: <?= $globalUser->getRank() ?><br/>
<b><?= Yii::t('user', 'Real confirmations') ?></b>: <?= $globalUser->getRealConfirmations() ?><br/>
<?php endif; ?>
