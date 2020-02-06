<?php

use app\modules\bot\components\CommandRouter;

return [
    'components' => [
        'commandRouter' => [
            'class' => CommandRouter::className(),
            'invalidRouteRedirect' => 'command-not-found',
            'rules' => [
                '/my_language_<language:\w+>' => 'my_language/index',
                '@language_list' => 'my_language/language-list',
                '@language_list_<page:\d+>' => 'my_language/language-list',

				'/my_currency_<currency:\w+>' => 'my_currency/index',
                '@currency_list' => 'my_currency/currency-list',
                '@currency_list_<page:\d+>' => 'my_currency/currency-list',

                '/set_email' => 'my_email/create',
                '@change_email' => 'my_email/update',

                '/set_birthday' => 'my_birthday/create',
                '@change_birthday' => 'my_birthday/update',

				'/<controller:\w+>' => '<controller>/index',
            ],
        ],
    ],
];
