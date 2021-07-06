<?php

return [
    'adminEmail' => 'admin@example.com',
    'securityEmail' => 'security@example.com',
    'user.passwordResetTokenExpire' => 3600 * 24, // 3600 miliseconds * 24 hours
    'baseUrl' => 'https://opensourcewebsite.org', // Used for /commands and Telegram webhooks
    //'telegramProxy' => '', // Examples: 'socks5://user:password@address:port', 'socks5://address:port', 'https://address:port'
    //'bot' => [
    //    'ua_lawmaking' => [
    //        'chat_id' => null,
    //    ],
    //],
    //'stellar' => // Used for stellar fund and telegram bot
    //[
    //    'testNet' => true, // https://developers.stellar.org/docs/glossary/testnet/
    //    'issuer_public_key' => '',
    //    'distributor_public_key' => '',
    //    'operator_public_key' => '',
    //    'operator_private_key' => '',
    //],
];
