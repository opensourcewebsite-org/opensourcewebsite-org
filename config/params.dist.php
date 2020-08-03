<?php

return [
    'adminEmail' => 'admin@example.com',
    'securityEmail' => 'security@example.com',
    'user.passwordResetTokenExpire' => 3600 * 24, //3600 miliseconds * 24 hours
    'baseUrl' => 'https://opensourcewebsite.org', //Used for /commands and Telegram webhooks
	//'telegramProxy' => '', //Examples: 'socks5://user:password@address:port', 'socks5://address:port', 'https://address:port'
    //Used for exchange rates
    'currencyRateAPI' => 'https://v6.exchangerate-api.com/v6/',
    'currencyRateToken' => '', //
    'currencyRateBase' => 'USD'
];
