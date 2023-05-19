<?php

return [
    'adminEmail' => 'admin@example.com',
    'securityEmail' => 'security@example.com',
    'baseUrl' => 'https://opensourcewebsite.org', // Used for /commands and Telegram webhooks
    // TODO remove 'telegramProxy' => '', // Examples: 'socks5://user:password@address:port', 'socks5://address:port', 'https://address:port'
    'bot' => [
        //'proxy' => '', // Examples: 'socks5://user:password@address:port', 'socks5://address:port', 'https://address:port'
        'username' => '',
        'token' => '',
        'chats' => [
            'osw_server_logs' => [
                'chat_id' => '',
            ],
            'osw_group' => [
                'chat_id' => '',
            ],
            'osw_channel' => [
                'chat_id' => '',
            ],
        ],
    ],
    //'stellar' => [ // Used for stellar features and Telegram bot
    //    'testNet' => true, // https://developers.stellar.org/docs/glossary/testnet/
    //    'issuer_public_key' => '',
    //    'distributor_public_key' => '',
    //    'operator_public_key' => '',
    //    'operator_private_key' => '',
    //],
];
