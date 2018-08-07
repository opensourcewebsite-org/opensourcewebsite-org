<?php

/* @var $this \yii\web\View */

use yii\helpers\Html;

$this->title = 'Donate';
?>
<div class="site-contact">
    <h1><?= Html::encode($this->title) ?></h1>

<p>
    OpenSourceWebsite is entirely supported by the general public.
</p>

<p>
    Your donations increase your social rating on this Website. Your donations pay for staff, servers, bandwidth, maintenance, development and protective infrastructure.
</p>

<h4>PayPal and credit cards</h4>

<p>
    <ul>
        <li>Processing with <?= Html::a('PayPal.Me', 'https://paypal.me/opensourcewebsite') ?></li>
    </ul>
</p>

<h4>Credit cards</h4>

<p>
    <ul>
        <li>Processing with <?= Html::a('SnowballFundraising.com', 'https://opensourcewebsite.snwbll.com/giving-portal') ?></li>
    </ul>
</p>

<h4>Cryptocurrencies</h4>

<p>
    <ul>
        <li>50+ cryptocurrencies. Processing with <?= Html::a('CoinGate.com', 'https://coingate.com/pay/opensourcewebsite') ?></li>
        <li>4 cryptocurrencies. Processing with <?= Html::a('Coinbase.com', 'https://commerce.coinbase.com/checkout/e89005ec-c8c2-47c1-9ca4-b1deb9992794') ?></li>
    </ul>
</p>

<p>
    For questions and suggestions, please <a href="/contact">contact</a> us.
</p>

</div>
