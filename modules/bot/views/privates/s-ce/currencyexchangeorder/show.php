<?php
/** @var \app\models\CurrencyExchangeOrder $order */
$order = $currencyexchangeorder
?>
<b><?= $order->sellingCurrency->code .'/'. $order->buyingCurrency->code ?> order #<?= $order->id ?></b><br/>
<br/>
Sell: <b><?= $order->sellingCurrency->code ?></b><br/>
Amount: <?= $order->selling_currency_min_amount .' - '. $order->selling_currency_max_amount ?><br/>
Payment methods:<br/>
- Cash<br/>
- Online System 1<br/>
<br/>
Buy: <b><?= $order->buyingCurrency->code ?></b><br/>
Payment methods:<br/>
- Cash<br/>
- Bank 1<br/>
<br/>
This post is active for 14 more days. Check this post again before this term to automatically renew this.
