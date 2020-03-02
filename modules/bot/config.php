<?php

use app\modules\bot\components\CommandRouteResolver;
use app\modules\bot\components\request\MessageRequestHandler;
use app\modules\bot\components\request\CallbackQueryRequestHandler;
use app\modules\bot\components\request\LocationRequestHandler;

return [
    'components' => [
        'commandRouteResolver' => [
            'class' => CommandRouteResolver::className(),
            'rules' => [
                '⚙️' => 'help/index',

                '/my_language_<language:\w+>' => 'my_language/index',
                '/language_list' => 'my_language/language-list',
                '/language_list_<page:\d+>' => 'my_language/language-list',

                '/my_currency_<currency:\w+>' => 'my_currency/index',
                '/currency_list' => 'my_currency/currency-list',
                '/currency_list_<page:\d+>' => 'my_currency/currency-list',

                '/set_email' => 'my_email/create',
                '/change_email' => 'my_email/update',
                '/merge_accounts' => 'my_email/merge-accounts',
                '/discard_merge_request <mergeAccountsRequestId:\d+>' => 'my_email/discard-merge-request',

                '/my_birthday_create' => 'my_birthday/create',
                '/my_birthday_update' => 'my_birthday/update',

                '/my_gender_update' => 'my_gender/update',
                '/my_gender_<gender:\w+>' => 'my_gender/index',

                '/update_location' => 'my_location/update',

                '/filterchat <groupId:\d+>' => 'filterchat',
                '/change_filter_mode <groupId:\d+>' => 'filterchat/update',
                '/whitelist <groupId:\d+>' => 'whitelist',
                '/blacklist <groupId:\d+>' => 'blacklist',
                '/newphrase <type:\w+> <groupId:\d+>' => 'newphrase/index',
                '/set_newphrase <type:\w+> <groupId:\d+>' => 'newphrase/update',

                '/phrase <phraseId:\d+>' => 'phrase/index',
                '/delete_phrase <phraseId:\d+>' => 'phrase/delete',
                '/change_phrase <phraseId:\d+>' => 'phrase/create',
                '/update_phrase <phraseId:\d+>' => 'phrase/update',

                '/<controller:\w+> <message:.+>' => '<controller>/index',
                '/<controller:\w+>' => '<controller>/index',
            ],
            'requestHandlers' => [
                new MessageRequestHandler(),
                new CallbackQueryRequestHandler(),
                new LocationRequestHandler(),
            ],
        ],
    ],
];
